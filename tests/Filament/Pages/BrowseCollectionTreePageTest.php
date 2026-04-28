<?php

namespace Tests\Filament\Pages;

use App\Enums\Permission;
use App\Filament\Pages\BrowseCollectionTree;
use App\Filament\Resources\CollectionResource;
use App\Models\Collection;
use App\Models\Context;
use App\Models\Language;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class BrowseCollectionTreePageTest extends TestCase
{
    use RefreshDatabase;

    protected function createViewUser(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::VIEW_DATA->value,
        ]);

        return $user;
    }

    protected function setCurrentPanel(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    /**
     * Bulk-insert root collections reusing a single language + context pair.
     */
    protected function seedRootCollections(int $count, string $prefix = 'Root'): void
    {
        $language = Language::factory()->create(['id' => 'eng', 'internal_name' => 'English', 'is_default' => true]);
        $context = Context::factory()->create(['internal_name' => 'Catalogue', 'is_default' => true]);

        $timestamp = Carbon::now();
        $rows = [];

        for ($i = 1; $i <= $count; $i++) {
            $rows[] = [
                'id' => (string) Str::uuid(),
                'internal_name' => sprintf('%s Collection %04d', $prefix, $i),
                'backward_compatibility' => sprintf('%s-%04d', strtolower($prefix), $i),
                'type' => 'collection',
                'language_id' => $language->id,
                'context_id' => $context->id,
                'parent_id' => null,
                'display_order' => null,
                'latitude' => null,
                'longitude' => null,
                'map_zoom' => null,
                'country_id' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            Collection::query()->insert($chunk);
        }
    }

    // -------------------------------------------------------------------------
    // Basic rendering
    // -------------------------------------------------------------------------

    public function test_page_renders_for_authorised_user(): void
    {
        $user = $this->createViewUser();

        $this->actingAs($user)->get('/admin/browse-collection-tree')->assertOk();
    }

    // -------------------------------------------------------------------------
    // Search by internal_name
    // -------------------------------------------------------------------------

    public function test_search_filters_roots_by_internal_name(): void
    {
        $user = $this->createViewUser();

        $language = Language::factory()->create(['id' => 'eng', 'internal_name' => 'English', 'is_default' => true]);
        $context = Context::factory()->create(['internal_name' => 'Catalogue', 'is_default' => true]);

        Collection::factory()->withLanguage($language->id)->withContext($context->id)->create([
            'internal_name' => 'Egyptian Gallery',
            'parent_id' => null,
        ]);
        Collection::factory()->withLanguage($language->id)->withContext($context->id)->create([
            'internal_name' => 'Bronze Age',
            'parent_id' => null,
        ]);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(BrowseCollectionTree::class)
            ->set('search', 'Egyptian')
            ->assertSee('Egyptian Gallery')
            ->assertDontSee('Bronze Age');
    }

    // -------------------------------------------------------------------------
    // Search by backward_compatibility
    // -------------------------------------------------------------------------

    public function test_search_filters_roots_by_backward_compatibility(): void
    {
        $user = $this->createViewUser();

        $language = Language::factory()->create(['id' => 'eng', 'internal_name' => 'English', 'is_default' => true]);
        $context = Context::factory()->create(['internal_name' => 'Catalogue', 'is_default' => true]);

        Collection::factory()->withLanguage($language->id)->withContext($context->id)->create([
            'internal_name' => 'Alpha Collection',
            'backward_compatibility' => 'leg-alpha',
            'parent_id' => null,
        ]);
        Collection::factory()->withLanguage($language->id)->withContext($context->id)->create([
            'internal_name' => 'Beta Collection',
            'backward_compatibility' => 'leg-beta',
            'parent_id' => null,
        ]);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(BrowseCollectionTree::class)
            ->set('search', 'leg-alpha')
            ->assertSee('Alpha Collection')
            ->assertDontSee('Beta Collection');
    }

    // -------------------------------------------------------------------------
    // Pagination: more roots than one page
    // -------------------------------------------------------------------------

    public function test_pagination_shows_first_page_when_roots_exceed_page_size(): void
    {
        $user = $this->createViewUser();

        // 51 roots → 2 pages.
        $this->seedRootCollections(51);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(BrowseCollectionTree::class)
            ->assertSee('Page 1 of 2');
    }

    public function test_next_page_advances_to_second_page(): void
    {
        $user = $this->createViewUser();

        $this->seedRootCollections(51);

        // Create an alphabetically-last item that lands on page 2.
        $language = Language::factory()->create(['id' => 'fra', 'internal_name' => 'French', 'is_default' => false]);
        $context = Context::factory()->create(['internal_name' => 'Other', 'is_default' => false]);
        Collection::factory()->withLanguage($language->id)->withContext($context->id)->create([
            'internal_name' => 'Zzz Unique Last Collection',
            'parent_id' => null,
        ]);
        // 52 roots → page 2 has 2 items.

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(BrowseCollectionTree::class)
            ->assertDontSee('Zzz Unique Last Collection')
            ->call('nextPage')
            ->assertSee('Page 2 of 2')
            ->assertSee('Zzz Unique Last Collection');
    }

    public function test_previous_page_returns_to_first_page(): void
    {
        $user = $this->createViewUser();

        $this->seedRootCollections(51);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(BrowseCollectionTree::class)
            ->call('nextPage')
            ->assertSee('Page 2 of 2')
            ->call('previousPage')
            ->assertSee('Page 1 of 2');
    }

    public function test_search_resets_page_to_first(): void
    {
        $user = $this->createViewUser();

        $this->seedRootCollections(51);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(BrowseCollectionTree::class)
            ->call('nextPage')
            ->assertSet('page', 2)
            ->set('search', 'something')
            ->assertSet('page', 1);
    }

    // -------------------------------------------------------------------------
    // Count / subset messaging
    // -------------------------------------------------------------------------

    public function test_root_count_messaging_is_shown(): void
    {
        $user = $this->createViewUser();

        $language = Language::factory()->create(['id' => 'eng', 'internal_name' => 'English', 'is_default' => true]);
        $context = Context::factory()->create(['internal_name' => 'Catalogue', 'is_default' => true]);

        Collection::factory()->withLanguage($language->id)->withContext($context->id)->create([
            'internal_name' => 'Visible Root',
            'parent_id' => null,
        ]);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(BrowseCollectionTree::class)
            ->assertSee('1 root collection');
    }

    public function test_search_result_count_is_shown(): void
    {
        $user = $this->createViewUser();

        $language = Language::factory()->create(['id' => 'eng', 'internal_name' => 'English', 'is_default' => true]);
        $context = Context::factory()->create(['internal_name' => 'Catalogue', 'is_default' => true]);

        Collection::factory()->withLanguage($language->id)->withContext($context->id)
            ->create(['internal_name' => 'Temple Gallery', 'parent_id' => null]);
        Collection::factory()->withLanguage($language->id)->withContext($context->id)
            ->create(['internal_name' => 'Temple Trail', 'parent_id' => null]);
        Collection::factory()->withLanguage($language->id)->withContext($context->id)
            ->create(['internal_name' => 'Bronze Age', 'parent_id' => null]);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(BrowseCollectionTree::class)
            ->set('search', 'Temple')
            ->assertSee('2 results');
    }

    // -------------------------------------------------------------------------
    // Node links
    // -------------------------------------------------------------------------

    public function test_tree_node_label_links_to_collection_resource_view_page(): void
    {
        $user = $this->createViewUser();

        $language = Language::factory()->create(['id' => 'eng', 'internal_name' => 'English', 'is_default' => true]);
        $context = Context::factory()->create(['internal_name' => 'Catalogue', 'is_default' => true]);

        $collection = Collection::factory()->withLanguage($language->id)->withContext($context->id)->create([
            'internal_name' => 'Linked Collection',
            'parent_id' => null,
        ]);

        $this->setCurrentPanel();

        $viewUrl = CollectionResource::getUrl('view', ['record' => $collection->getKey()]);

        Livewire::actingAs($user)
            ->test(BrowseCollectionTree::class)
            ->assertSee($viewUrl, false);
    }

    // -------------------------------------------------------------------------
    // Non-root collections are not shown as roots
    // -------------------------------------------------------------------------

    public function test_child_collections_do_not_appear_as_roots(): void
    {
        $user = $this->createViewUser();

        $language = Language::factory()->create(['id' => 'eng', 'internal_name' => 'English', 'is_default' => true]);
        $context = Context::factory()->create(['internal_name' => 'Catalogue', 'is_default' => true]);

        $parent = Collection::factory()->withLanguage($language->id)->withContext($context->id)->create([
            'internal_name' => 'Parent Collection',
            'parent_id' => null,
        ]);
        Collection::factory()->withLanguage($language->id)->withContext($context->id)->create([
            'internal_name' => 'Child Collection',
            'parent_id' => $parent->getKey(),
        ]);

        $this->setCurrentPanel();

        // Default (no expansion) → only root shown.
        Livewire::actingAs($user)
            ->test(BrowseCollectionTree::class)
            ->assertSee('Parent Collection')
            ->assertDontSee('Child Collection');
    }
}

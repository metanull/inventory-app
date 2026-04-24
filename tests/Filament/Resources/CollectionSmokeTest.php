<?php

namespace Tests\Filament\Resources;

use App\Enums\Permission;
use App\Filament\Pages\BrowseCollectionTree;
use App\Filament\Resources\CollectionResource\Pages\ListCollection;
use App\Models\Collection;
use App\Models\Context;
use App\Models\Language;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class CollectionSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_collection_resource_handles_a_ten_thousand_row_dataset(): void
    {
        $user = $this->createAuthorizedUser();
        $this->seedCollections();

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->actingAs($user)->get('/admin/collections');

        $response->assertOk();
        $this->assertLessThan(50, count(DB::getQueryLog()));
        $this->assertLessThan(2 * 1024 * 1024, strlen($response->getContent()));
        DB::disableQueryLog();

        $this->setCurrentPanel();

        $expectedFirstPage = Collection::query()
            ->orderBy('internal_name')
            ->limit(10)
            ->get();

        $target = Collection::query()->where('internal_name', 'Collection 09999')->firstOrFail();
        $nonTarget = Collection::query()->where('internal_name', 'Collection 00000')->firstOrFail();

        Livewire::actingAs($user)
            ->test(ListCollection::class)
            ->assertCanSeeTableRecords($expectedFirstPage, inOrder: true)
            ->searchTable('Collection 09999')
            ->assertCanSeeTableRecords([$target])
            ->assertCanNotSeeTableRecords([$nonTarget]);
    }

    public function test_browse_collection_tree_expands_five_levels_deep(): void
    {
        $user = $this->createAuthorizedUser();
        $hierarchy = $this->seedHierarchy(5);

        $this->setCurrentPanel();

        $component = Livewire::actingAs($user)
            ->test(BrowseCollectionTree::class);

        $component->assertSee($hierarchy[0]->internal_name);

        // Expand each level
        foreach ($hierarchy as $depth => $node) {
            $component->call('expand', $node->id);

            if ($depth + 1 < count($hierarchy)) {
                $component->assertSee($hierarchy[$depth + 1]->internal_name);
            }
        }

        $this->assertLessThan(20 * 1024 * 1024, strlen($component->html()));
    }

    protected function createAuthorizedUser(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::VIEW_DATA->value,
        ]);

        return $user;
    }

    protected function seedCollections(): void
    {
        $language = Language::factory()->create(['id' => 'eng', 'internal_name' => 'English']);
        $context = Context::factory()->create(['internal_name' => 'Catalogue']);
        $timestamp = Carbon::now();

        $rows = [];
        for ($i = 0; $i < 10_000; $i++) {
            $rows[] = [
                'id' => (string) Str::uuid(),
                'internal_name' => sprintf('Collection %05d', $i),
                'type' => 'collection',
                'backward_compatibility' => sprintf('col-%05d', $i),
                'language_id' => $language->id,
                'context_id' => $context->id,
                'parent_id' => null,
                'country_id' => null,
                'display_order' => null,
                'latitude' => null,
                'longitude' => null,
                'map_zoom' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        foreach (array_chunk($rows, 1000) as $chunk) {
            Collection::query()->insert($chunk);
        }
    }

    /**
     * Seed a chain of collections: root → level1 → level2 → … → level(depth-1).
     *
     * @return array<int, Collection>
     */
    protected function seedHierarchy(int $depth): array
    {
        $nodes = [];
        $parentId = null;

        for ($i = 0; $i < $depth; $i++) {
            $node = Collection::factory()->create([
                'internal_name' => sprintf('Hierarchy Level %d', $i),
                'type' => 'collection',
                'parent_id' => $parentId,
            ]);
            $nodes[] = $node;
            $parentId = $node->id;
        }

        return $nodes;
    }

    protected function setCurrentPanel(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }
}

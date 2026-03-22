<?php

namespace Tests\Web\Livewire;

use App\Livewire\Tables\CollectionsTable;
use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class CollectionsTableTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_component_can_render(): void
    {
        Livewire::test(CollectionsTable::class)->assertOk();
    }

    public function test_hierarchy_mode_shows_root_collections_by_default(): void
    {
        $root = Collection::factory()->create(['internal_name' => 'Root Collection']);
        $child = Collection::factory()->create([
            'internal_name' => 'Child Collection',
            'parent_id' => $root->id,
            'language_id' => $root->language_id,
            'context_id' => $root->context_id,
        ]);

        Livewire::test(CollectionsTable::class)
            ->assertSee('Root Collection')
            ->assertDontSee('Child Collection');
    }

    public function test_navigate_to_children(): void
    {
        $root = Collection::factory()->create(['internal_name' => 'Root Collection']);
        $child = Collection::factory()->create([
            'internal_name' => 'Child Collection',
            'parent_id' => $root->id,
            'language_id' => $root->language_id,
            'context_id' => $root->context_id,
        ]);

        Livewire::test(CollectionsTable::class)
            ->call('navigateToParent', $root->id)
            ->assertSet('parentId', $root->id)
            ->assertSee('Child Collection');
    }

    public function test_navigate_back_to_root(): void
    {
        $root = Collection::factory()->create(['internal_name' => 'Root Collection']);
        $child = Collection::factory()->create([
            'internal_name' => 'Child Collection',
            'parent_id' => $root->id,
            'language_id' => $root->language_id,
            'context_id' => $root->context_id,
        ]);

        Livewire::test(CollectionsTable::class)
            ->call('navigateToParent', $root->id)
            ->assertSee('Child Collection')
            ->call('navigateUp')
            ->assertSee('Root Collection')
            ->assertDontSee('Child Collection');
    }

    public function test_flat_mode_shows_all_collections(): void
    {
        $root = Collection::factory()->create(['internal_name' => 'Root Collection']);
        $child = Collection::factory()->create([
            'internal_name' => 'Child Collection',
            'parent_id' => $root->id,
            'language_id' => $root->language_id,
            'context_id' => $root->context_id,
        ]);

        Livewire::test(CollectionsTable::class)
            ->call('toggleHierarchyMode')
            ->assertSee('Root Collection')
            ->assertSee('Child Collection');
    }

    public function test_breadcrumbs_display_when_navigated_into_parent(): void
    {
        $root = Collection::factory()->create(['internal_name' => 'Root Collection']);
        $child = Collection::factory()->create([
            'internal_name' => 'Child Collection',
            'parent_id' => $root->id,
            'language_id' => $root->language_id,
            'context_id' => $root->context_id,
        ]);

        Livewire::test(CollectionsTable::class)
            ->call('navigateToParent', $root->id)
            ->assertSee('All Collections')
            ->assertSee('Root Collection');
    }

    public function test_children_count_displays_in_hierarchy_mode(): void
    {
        $root = Collection::factory()->create(['internal_name' => 'Root Collection']);
        Collection::factory()->count(3)->create([
            'parent_id' => $root->id,
            'language_id' => $root->language_id,
            'context_id' => $root->context_id,
        ]);

        Livewire::test(CollectionsTable::class)
            ->assertSeeInOrder(['Root Collection', '3']);
    }

    public function test_search_works_in_hierarchy_mode(): void
    {
        $root1 = Collection::factory()->create(['internal_name' => 'Alpha Root']);
        $root2 = Collection::factory()->create(['internal_name' => 'Beta Root']);

        Livewire::test(CollectionsTable::class)
            ->set('q', 'Alpha')
            ->assertSee('Alpha Root')
            ->assertDontSee('Beta Root');
    }

    public function test_toggle_resets_parent_id(): void
    {
        $root = Collection::factory()->create(['internal_name' => 'Root Collection']);

        Livewire::test(CollectionsTable::class)
            ->call('navigateToParent', $root->id)
            ->assertSet('parentId', $root->id)
            ->call('toggleHierarchyMode')
            ->assertSet('parentId', '')
            ->assertSet('hierarchyMode', false);
    }

    public function test_multi_level_breadcrumbs(): void
    {
        $grandparent = Collection::factory()->create(['internal_name' => 'Grandparent']);
        $parent = Collection::factory()->create([
            'internal_name' => 'Parent',
            'parent_id' => $grandparent->id,
            'language_id' => $grandparent->language_id,
            'context_id' => $grandparent->context_id,
        ]);
        $child = Collection::factory()->create([
            'internal_name' => 'Child',
            'parent_id' => $parent->id,
            'language_id' => $grandparent->language_id,
            'context_id' => $grandparent->context_id,
        ]);

        Livewire::test(CollectionsTable::class)
            ->call('navigateToParent', $parent->id)
            ->assertSee('Grandparent')
            ->assertSee('Parent')
            ->assertSee('All Collections');
    }
}

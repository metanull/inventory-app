<?php

namespace Tests\Web\Livewire;

use App\Livewire\Tables\ItemsTable;
use App\Models\Item;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class ItemsTableTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_component_can_render(): void
    {
        Livewire::test(ItemsTable::class)->assertOk();
    }

    public function test_hierarchy_mode_shows_root_items_by_default(): void
    {
        $root = Item::factory()->create(['internal_name' => 'Root Item']);
        $child = Item::factory()->create([
            'internal_name' => 'Child Item',
            'parent_id' => $root->id,
        ]);

        Livewire::test(ItemsTable::class)
            ->assertSee('Root Item')
            ->assertDontSee('Child Item');
    }

    public function test_navigate_to_children(): void
    {
        $root = Item::factory()->create(['internal_name' => 'Root Item']);
        $child = Item::factory()->create([
            'internal_name' => 'Child Item',
            'parent_id' => $root->id,
        ]);

        Livewire::test(ItemsTable::class)
            ->call('navigateToParent', $root->id)
            ->assertSet('parentId', $root->id)
            ->assertSee('Child Item');
    }

    public function test_navigate_back_to_root(): void
    {
        $root = Item::factory()->create(['internal_name' => 'Root Item']);
        $child = Item::factory()->create([
            'internal_name' => 'Child Item',
            'parent_id' => $root->id,
        ]);

        Livewire::test(ItemsTable::class)
            ->call('navigateToParent', $root->id)
            ->assertSee('Child Item')
            ->call('navigateUp')
            ->assertSee('Root Item')
            ->assertDontSee('Child Item');
    }

    public function test_flat_mode_shows_all_items(): void
    {
        $root = Item::factory()->create(['internal_name' => 'Root Item']);
        $child = Item::factory()->create([
            'internal_name' => 'Child Item',
            'parent_id' => $root->id,
        ]);

        Livewire::test(ItemsTable::class)
            ->call('toggleHierarchyMode')
            ->assertSee('Root Item')
            ->assertSee('Child Item');
    }

    public function test_breadcrumbs_display_when_navigated_into_parent(): void
    {
        $root = Item::factory()->create(['internal_name' => 'Root Item']);
        $child = Item::factory()->create([
            'internal_name' => 'Child Item',
            'parent_id' => $root->id,
        ]);

        Livewire::test(ItemsTable::class)
            ->call('navigateToParent', $root->id)
            ->assertSee('All Items')
            ->assertSee('Root Item');
    }

    public function test_children_count_displays_in_hierarchy_mode(): void
    {
        $root = Item::factory()->create(['internal_name' => 'Root Item']);
        Item::factory()->count(3)->create([
            'parent_id' => $root->id,
        ]);

        Livewire::test(ItemsTable::class)
            ->assertSeeInOrder(['Root Item', '3']);
    }

    public function test_search_works_in_hierarchy_mode(): void
    {
        $root1 = Item::factory()->create(['internal_name' => 'Alpha Root']);
        $root2 = Item::factory()->create(['internal_name' => 'Beta Root']);

        Livewire::test(ItemsTable::class)
            ->set('q', 'Alpha')
            ->assertSee('Alpha Root')
            ->assertDontSee('Beta Root');
    }

    public function test_toggle_resets_parent_id(): void
    {
        $root = Item::factory()->create(['internal_name' => 'Root Item']);

        Livewire::test(ItemsTable::class)
            ->call('navigateToParent', $root->id)
            ->assertSet('parentId', $root->id)
            ->call('toggleHierarchyMode')
            ->assertSet('parentId', '')
            ->assertSet('hierarchyMode', false);
    }

    public function test_type_filter_works_with_hierarchy_mode(): void
    {
        $root1 = Item::factory()->create([
            'internal_name' => 'Object Root',
            'type' => 'object',
        ]);
        $root2 = Item::factory()->create([
            'internal_name' => 'Monument Root',
            'type' => 'monument',
        ]);

        Livewire::test(ItemsTable::class)
            ->set('typeFilter', 'object')
            ->assertSee('Object Root')
            ->assertDontSee('Monument Root');
    }

    public function test_tag_filter_works_with_hierarchy_mode(): void
    {
        $tag = Tag::factory()->create(['internal_name' => 'TestTag']);
        $tagged = Item::factory()->create(['internal_name' => 'Tagged Root']);
        $tagged->tags()->attach($tag);
        $untagged = Item::factory()->create(['internal_name' => 'Untagged Root']);

        Livewire::test(ItemsTable::class)
            ->set('selectedTags', [$tag->id])
            ->assertSee('Tagged Root')
            ->assertDontSee('Untagged Root');
    }

    public function test_multi_level_breadcrumbs(): void
    {
        $grandparent = Item::factory()->create(['internal_name' => 'Grandparent']);
        $parent = Item::factory()->create([
            'internal_name' => 'Parent',
            'parent_id' => $grandparent->id,
        ]);
        $child = Item::factory()->create([
            'internal_name' => 'Child',
            'parent_id' => $parent->id,
        ]);

        Livewire::test(ItemsTable::class)
            ->call('navigateToParent', $parent->id)
            ->assertSee('Grandparent')
            ->assertSee('Parent')
            ->assertSee('All Items');
    }
}

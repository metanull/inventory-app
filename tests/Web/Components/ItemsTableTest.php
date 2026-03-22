<?php

namespace Tests\Web\Components;

use App\Livewire\Tables\ItemsTable;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebLivewire;

class ItemsTableTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebLivewire;

    protected function getComponentClass(): string
    {
        return ItemsTable::class;
    }

    protected function getModelClass(): string
    {
        return Item::class;
    }

    protected function getIdentifier($model): string
    {
        return $model->internal_name;
    }

    public function test_hierarchy_mode_shows_only_root_items_by_default(): void
    {
        $root = Item::factory()->create(['parent_id' => null]);
        $child = Item::factory()->create(['parent_id' => $root->id]);

        $component = Livewire::test(ItemsTable::class);

        $component->assertSee($root->internal_name)
            ->assertDontSee($child->internal_name);
    }

    public function test_flat_mode_shows_all_items(): void
    {
        $root = Item::factory()->create(['parent_id' => null]);
        $child = Item::factory()->create(['parent_id' => $root->id]);

        $component = Livewire::test(ItemsTable::class)
            ->call('toggleHierarchyMode');

        $component->assertSee($root->internal_name)
            ->assertSee($child->internal_name);
    }

    public function test_navigate_to_parent_shows_children(): void
    {
        $root = Item::factory()->create(['parent_id' => null]);
        $child = Item::factory()->create(['parent_id' => $root->id]);
        $sibling = Item::factory()->create(['parent_id' => null]);

        $component = Livewire::test(ItemsTable::class)
            ->call('navigateToParent', $root->id);

        $component->assertSee($child->internal_name)
            ->assertDontSee($sibling->internal_name);
    }

    public function test_navigate_up_returns_to_parent_level(): void
    {
        $root = Item::factory()->create(['parent_id' => null]);
        $child = Item::factory()->create(['parent_id' => $root->id]);

        $component = Livewire::test(ItemsTable::class)
            ->call('navigateToParent', $root->id)
            ->call('navigateUp');

        $component->assertSee($root->internal_name)
            ->assertDontSee($child->internal_name);
    }

    public function test_navigate_up_from_root_stays_at_root(): void
    {
        $root = Item::factory()->create(['parent_id' => null]);

        $component = Livewire::test(ItemsTable::class)
            ->call('navigateUp');

        $component->assertSet('parentId', '')
            ->assertSee($root->internal_name);
    }

    public function test_children_count_is_displayed(): void
    {
        $root = Item::factory()->create(['parent_id' => null]);
        Item::factory()->count(2)->create(['parent_id' => $root->id]);

        $component = Livewire::test(ItemsTable::class);

        $component->assertSee('2');
    }

    public function test_toggle_hierarchy_mode_resets_parent(): void
    {
        $root = Item::factory()->create(['parent_id' => null]);

        $component = Livewire::test(ItemsTable::class)
            ->call('navigateToParent', $root->id)
            ->call('toggleHierarchyMode');

        $component->assertSet('parentId', '')
            ->assertSet('hierarchyMode', false);
    }

    public function test_type_filter_works_with_hierarchy_mode(): void
    {
        $root = Item::factory()->create(['parent_id' => null, 'type' => 'object']);
        $otherRoot = Item::factory()->create(['parent_id' => null, 'type' => 'monument']);

        $component = Livewire::test(ItemsTable::class)
            ->set('typeFilter', 'object');

        $component->assertSee($root->internal_name)
            ->assertDontSee($otherRoot->internal_name);
    }
}

<?php

namespace Tests\Web\Components;

use App\Livewire\Tables\CollectionsTable;
use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebLivewire;

class CollectionsTableTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebLivewire;

    protected function getComponentClass(): string
    {
        return CollectionsTable::class;
    }

    protected function getModelClass(): string
    {
        return Collection::class;
    }

    protected function getIdentifier($model): string
    {
        return $model->internal_name;
    }

    protected function getSortableFields(): array
    {
        return ['internal_name', 'display_order', 'created_at'];
    }

    public function test_hierarchy_mode_shows_only_root_collections_by_default(): void
    {
        $root = Collection::factory()->create();
        $child = Collection::factory()->create(['parent_id' => $root->id]);

        $component = Livewire::test(CollectionsTable::class);

        $component->assertSee($root->internal_name)
            ->assertDontSee($child->internal_name);
    }

    public function test_flat_mode_shows_all_collections(): void
    {
        $root = Collection::factory()->create();
        $child = Collection::factory()->create(['parent_id' => $root->id]);

        $component = Livewire::test(CollectionsTable::class)
            ->call('toggleHierarchyMode');

        $component->assertSee($root->internal_name)
            ->assertSee($child->internal_name);
    }

    public function test_navigate_to_parent_shows_children(): void
    {
        $root = Collection::factory()->create();
        $child = Collection::factory()->create(['parent_id' => $root->id]);
        $sibling = Collection::factory()->create();

        $component = Livewire::test(CollectionsTable::class)
            ->call('navigateToParent', $root->id);

        $component->assertSee($child->internal_name)
            ->assertDontSee($sibling->internal_name);
    }

    public function test_navigate_up_returns_to_parent_level(): void
    {
        $root = Collection::factory()->create();
        $child = Collection::factory()->create(['parent_id' => $root->id]);

        $component = Livewire::test(CollectionsTable::class)
            ->call('navigateToParent', $root->id)
            ->call('navigateUp');

        $component->assertSee($root->internal_name)
            ->assertDontSee($child->internal_name);
    }

    public function test_navigate_up_from_root_stays_at_root(): void
    {
        $root = Collection::factory()->create();

        $component = Livewire::test(CollectionsTable::class)
            ->call('navigateUp');

        $component->assertSet('parentId', '')
            ->assertSee($root->internal_name);
    }

    public function test_children_count_is_displayed(): void
    {
        $root = Collection::factory()->create();
        Collection::factory()->count(2)->create(['parent_id' => $root->id]);

        $component = Livewire::test(CollectionsTable::class);

        $component->assertSee('2');
    }

    public function test_toggle_hierarchy_mode_resets_parent(): void
    {
        $root = Collection::factory()->create();

        $component = Livewire::test(CollectionsTable::class)
            ->call('navigateToParent', $root->id)
            ->call('toggleHierarchyMode');

        $component->assertSet('parentId', '')
            ->assertSet('hierarchyMode', false);
    }
}

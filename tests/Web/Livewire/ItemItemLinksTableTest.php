<?php

namespace Tests\Web\Livewire;

use App\Livewire\Tables\ItemItemLinksTable;
use App\Models\Item;
use App\Models\ItemItemLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class ItemItemLinksTableTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_component_can_render(): void
    {
        Livewire::test(ItemItemLinksTable::class)->assertOk();
    }

    public function test_component_renders_with_item_scope(): void
    {
        $item = Item::factory()->create();

        Livewire::test(ItemItemLinksTable::class, ['item' => $item])
            ->assertOk();
    }

    public function test_invalid_sort_field_falls_back_to_default(): void
    {
        $link = ItemItemLink::factory()->create();

        Livewire::test(ItemItemLinksTable::class, ['item' => $link->source])
            ->set('sortBy', 'nonexistent_column')
            ->assertOk();
    }

    public function test_sort_by_rejects_invalid_field(): void
    {
        Livewire::test(ItemItemLinksTable::class)
            ->call('sortBy', 'nonexistent_column')
            ->assertSet('sortBy', 'created_at');
    }

    public function test_sort_by_accepts_valid_field(): void
    {
        Livewire::test(ItemItemLinksTable::class)
            ->call('sortBy', 'updated_at')
            ->assertSet('sortBy', 'updated_at')
            ->assertSet('sortDirection', 'asc');
    }

    public function test_invalid_sort_direction_falls_back_to_desc(): void
    {
        $link = ItemItemLink::factory()->create();

        Livewire::test(ItemItemLinksTable::class, ['item' => $link->source])
            ->set('sortDirection', 'sideways')
            ->assertOk();
    }

    public function test_context_filter_works(): void
    {
        $link = ItemItemLink::factory()->create();
        $otherLink = ItemItemLink::factory()->create();

        Livewire::test(ItemItemLinksTable::class)
            ->set('contextFilter', $link->context_id)
            ->assertSee($link->target->internal_name)
            ->assertDontSee($otherLink->target->internal_name);
    }

    public function test_search_filters_by_target_name(): void
    {
        $source = Item::factory()->create();
        $target1 = Item::factory()->create(['internal_name' => 'Alpha Target']);
        $target2 = Item::factory()->create(['internal_name' => 'Beta Target']);
        ItemItemLink::factory()->create(['source_id' => $source->id, 'target_id' => $target1->id]);
        ItemItemLink::factory()->create(['source_id' => $source->id, 'target_id' => $target2->id]);

        Livewire::test(ItemItemLinksTable::class, ['item' => $source])
            ->set('q', 'Alpha')
            ->assertSee('Alpha Target')
            ->assertDontSee('Beta Target');
    }
}

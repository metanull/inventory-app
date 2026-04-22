<?php

namespace Tests\Unit\Livewire;

use App\Livewire\SearchableSelect;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests for SearchableSelect prefix-first search and configurable perPage (Story 2.3).
 *
 * Verifies that prefix matches appear before infix-only matches and that
 * the result set is bounded by the perPage prop (or its config default).
 */
class SearchableSelectSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_prefix_match_is_returned_before_infix_only_match(): void
    {
        // 'Alpha Item' starts with 'Alpha' (prefix match)
        $prefixItem = Item::factory()->create(['internal_name' => 'Alpha Item']);
        // 'Not Alpha' contains 'Alpha' but does not start with it (infix-only match)
        $infixItem = Item::factory()->create(['internal_name' => 'Not Alpha']);

        $component = Livewire::test(SearchableSelect::class, [
            'modelClass' => Item::class,
            'name' => 'item_id',
        ])->set('search', 'Alpha');

        $options = $component->get('options');

        $this->assertCount(2, $options);
        $this->assertEquals($prefixItem->id, $options->first()->id);
        $this->assertEquals($infixItem->id, $options->last()->id);
    }

    public function test_fallback_infix_match_appears_after_prefix_matches(): void
    {
        $prefixA = Item::factory()->create(['internal_name' => 'Alpha First']);
        $prefixB = Item::factory()->create(['internal_name' => 'Alpha Second']);
        $infixOnly = Item::factory()->create(['internal_name' => 'Not Alpha At All']);

        $component = Livewire::test(SearchableSelect::class, [
            'modelClass' => Item::class,
            'name' => 'item_id',
        ])->set('search', 'Alpha');

        $options = $component->get('options');
        $ids = $options->pluck('id')->all();

        // Both prefix matches come before the infix-only match
        $posA = array_search($prefixA->id, $ids);
        $posB = array_search($prefixB->id, $ids);
        $posInfix = array_search($infixOnly->id, $ids);

        $this->assertLessThan($posInfix, $posA);
        $this->assertLessThan($posInfix, $posB);
    }

    public function test_per_page_prop_limits_results(): void
    {
        Item::factory()->count(10)->create();

        $component = Livewire::test(SearchableSelect::class, [
            'modelClass' => Item::class,
            'name' => 'item_id',
            'perPage' => 3,
        ]);

        $this->assertCount(3, $component->get('options'));
    }

    public function test_per_page_config_default_is_honoured(): void
    {
        config()->set('interface.searchable_select.per_page', 4);

        Item::factory()->count(10)->create();

        $component = Livewire::test(SearchableSelect::class, [
            'modelClass' => Item::class,
            'name' => 'item_id',
        ]);

        $this->assertLessThanOrEqual(4, $component->get('options')->count());
    }

    public function test_empty_search_returns_results_ordered_alphabetically(): void
    {
        Item::factory()->create(['internal_name' => 'Zeta']);
        Item::factory()->create(['internal_name' => 'Alpha']);
        Item::factory()->create(['internal_name' => 'Mu']);

        $component = Livewire::test(SearchableSelect::class, [
            'modelClass' => Item::class,
            'name' => 'item_id',
        ])->set('search', '');

        $options = $component->get('options');
        $names = $options->pluck('internal_name')->all();

        $sorted = $names;
        sort($sorted);
        $this->assertEquals($sorted, $names);
    }

    public function test_no_results_returned_when_nothing_matches_search(): void
    {
        Item::factory()->create(['internal_name' => 'Alpha Item']);

        $component = Livewire::test(SearchableSelect::class, [
            'modelClass' => Item::class,
            'name' => 'item_id',
        ])->set('search', 'xyzxyzxyz');

        $this->assertCount(0, $component->get('options'));
    }
}

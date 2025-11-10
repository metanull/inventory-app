<?php

namespace Tests\Web\Livewire;

use App\Livewire\SearchableSelect;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests for SearchableSelect Livewire Component
 * 
 * Tests custom business logic only, not framework functionality.
 * Focuses on: static options, dynamic DB queries, filtering, and search.
 */
class SearchableSelectTest extends TestCase
{
    use RefreshDatabase;

    public function test_component_renders_with_static_options(): void
    {
        $options = collect([
            (object)['id' => '1', 'internal_name' => 'Option One'],
            (object)['id' => '2', 'internal_name' => 'Option Two'],
        ]);

        Livewire::test(SearchableSelect::class, [
            'staticOptions' => $options,
            'name' => 'test_field',
        ])
            ->assertOk();
    }

    public function test_component_renders_with_dynamic_model(): void
    {
        $item1 = Item::factory()->create(['internal_name' => 'Test Item 1']);
        $item2 = Item::factory()->create(['internal_name' => 'Test Item 2']);

        Livewire::test(SearchableSelect::class, [
            'modelClass' => Item::class,
            'name' => 'item_id',
        ])
            ->assertOk();
    }

    public function test_search_filters_static_options(): void
    {
        $options = collect([
            (object)['id' => '1', 'internal_name' => 'Alpha'],
            (object)['id' => '2', 'internal_name' => 'Beta'],
            (object)['id' => '3', 'internal_name' => 'Gamma'],
        ]);

        $component = Livewire::test(SearchableSelect::class, [
            'staticOptions' => $options,
            'name' => 'test_field',
        ])
            ->set('search', 'alph');
        
        $filteredOptions = $component->get('options');
        $this->assertTrue($filteredOptions->contains('internal_name', 'Alpha'));
        $this->assertFalse($filteredOptions->contains('internal_name', 'Beta'));
    }

    public function test_search_filters_dynamic_options(): void
    {
        Item::factory()->create(['internal_name' => 'Alpha Item']);
        Item::factory()->create(['internal_name' => 'Beta Item']);

        $component = Livewire::test(SearchableSelect::class, [
            'modelClass' => Item::class,
            'name' => 'item_id',
        ])
            ->set('search', 'Alpha');
        
        $filteredOptions = $component->get('options');
        $this->assertTrue($filteredOptions->contains('internal_name', 'Alpha Item'));
        $this->assertFalse($filteredOptions->contains('internal_name', 'Beta Item'));
    }

    public function test_select_option_sets_selected_id(): void
    {
        $item = Item::factory()->create();

        Livewire::test(SearchableSelect::class, [
            'modelClass' => Item::class,
            'name' => 'item_id',
        ])
            ->call('selectOption', $item->id)
            ->assertSet('selectedId', $item->id)
            ->assertSet('open', false);
    }

    public function test_clear_resets_selection(): void
    {
        $item = Item::factory()->create();

        Livewire::test(SearchableSelect::class, [
            'modelClass' => Item::class,
            'name' => 'item_id',
            'selectedId' => $item->id,
        ])
            ->call('clear')
            ->assertSet('selectedId', '')
            ->assertSet('search', '');
    }

    public function test_filter_excludes_specific_value(): void
    {
        $item1 = Item::factory()->create(['internal_name' => 'Item 1']);
        $item2 = Item::factory()->create(['internal_name' => 'Item 2']);

        $component = Livewire::test(SearchableSelect::class, [
            'modelClass' => Item::class,
            'name' => 'item_id',
            'filterColumn' => 'id',
            'filterOperator' => '!=',
            'filterValue' => $item1->id,
        ]);

        // Access the computed property to check filtering
        $options = $component->get('options');
        
        $this->assertFalse($options->contains('id', $item1->id));
        $this->assertTrue($options->contains('id', $item2->id));
    }

    public function test_filter_with_not_in_operator(): void
    {
        $item1 = Item::factory()->create(['internal_name' => 'Item 1']);
        $item2 = Item::factory()->create(['internal_name' => 'Item 2']);
        $item3 = Item::factory()->create(['internal_name' => 'Item 3']);

        $component = Livewire::test(SearchableSelect::class, [
            'modelClass' => Item::class,
            'name' => 'item_id',
            'filterColumn' => 'id',
            'filterOperator' => 'NOT IN',
            'filterValue' => [$item1->id, $item2->id],
        ]);

        $options = $component->get('options');
        
        $this->assertFalse($options->contains('id', $item1->id));
        $this->assertFalse($options->contains('id', $item2->id));
        $this->assertTrue($options->contains('id', $item3->id));
    }

    public function test_selected_option_returns_correct_model(): void
    {
        $item = Item::factory()->create(['internal_name' => 'Test Item']);

        $component = Livewire::test(SearchableSelect::class, [
            'modelClass' => Item::class,
            'name' => 'item_id',
            'selectedId' => $item->id,
        ]);

        $selectedOption = $component->get('selectedOption');
        
        $this->assertNotNull($selectedOption);
        $this->assertEquals($item->id, $selectedOption->id);
        $this->assertEquals('Test Item', $selectedOption->internal_name);
    }

    public function test_selected_option_with_static_options(): void
    {
        $options = collect([
            (object)['id' => '1', 'internal_name' => 'Option One'],
            (object)['id' => '2', 'internal_name' => 'Option Two'],
        ]);

        $component = Livewire::test(SearchableSelect::class, [
            'staticOptions' => $options,
            'name' => 'test_field',
            'selectedId' => '2',
        ]);

        $selectedOption = $component->get('selectedOption');
        
        $this->assertEquals('2', $selectedOption->id);
        $this->assertEquals('Option Two', $selectedOption->internal_name);
    }

    public function test_search_opens_dropdown(): void
    {
        $item = Item::factory()->create();

        Livewire::test(SearchableSelect::class, [
            'modelClass' => Item::class,
            'name' => 'item_id',
        ])
            ->assertSet('open', false)
            ->set('search', 'test')
            ->assertSet('open', true);
    }

    public function test_limits_dynamic_results_to_50(): void
    {
        // Create 60 items
        Item::factory()->count(60)->create();

        $component = Livewire::test(SearchableSelect::class, [
            'modelClass' => Item::class,
            'name' => 'item_id',
        ]);

        $options = $component->get('options');
        
        $this->assertLessThanOrEqual(50, $options->count());
    }

    public function test_empty_search_shows_all_static_options(): void
    {
        $options = collect([
            (object)['id' => '1', 'internal_name' => 'Alpha'],
            (object)['id' => '2', 'internal_name' => 'Beta'],
            (object)['id' => '3', 'internal_name' => 'Gamma'],
        ]);

        $component = Livewire::test(SearchableSelect::class, [
            'staticOptions' => $options,
            'name' => 'test_field',
        ])
            ->set('search', '');

        $this->assertCount(3, $component->get('options'));
    }
}
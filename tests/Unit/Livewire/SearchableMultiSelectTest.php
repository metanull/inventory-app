<?php

namespace Tests\Unit\Livewire;

use App\Livewire\SearchableMultiSelect;
use App\Models\Item;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Unit tests for the SearchableMultiSelect Livewire component (Story 3.3).
 *
 * Covers:
 *   1. Initial state: no selected ids.
 *   2. addOption($id) appends once; duplicate adds are no-ops.
 *   3. removeOption($id) removes by id.
 *   4. clear() empties the selection.
 *   5. Search composes with scopes.
 *   6. Ceiling guard (from Story 2.1) still applies when staticOptions is used.
 *   7. selectedOptions collection renders chips without issuing a new query.
 */
class SearchableMultiSelectTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Walk the exception chain to find the root cause.
     * Livewire 3 wraps mount() exceptions in ViewException.
     */
    private function getRootException(\Throwable $e): \Throwable
    {
        while ($e->getPrevious() !== null) {
            $e = $e->getPrevious();
        }

        return $e;
    }

    // 1. Initial state: no selected ids ───────────────────────────────────────

    public function test_initial_state_has_no_selected_ids(): void
    {
        $component = Livewire::test(SearchableMultiSelect::class, [
            'modelClass' => Item::class,
            'name' => 'item_ids',
        ]);

        $this->assertEmpty($component->get('selectedIds'));
    }

    // 2. addOption: appends once, duplicate is a no-op ─────────────────────────

    public function test_add_option_appends_id(): void
    {
        $item = Item::factory()->create();

        $component = Livewire::test(SearchableMultiSelect::class, [
            'modelClass' => Item::class,
            'name' => 'item_ids',
        ]);

        $component->call('addOption', $item->id);

        $this->assertContains($item->id, $component->get('selectedIds'));
    }

    public function test_add_option_duplicate_is_a_noop(): void
    {
        $item = Item::factory()->create();

        $component = Livewire::test(SearchableMultiSelect::class, [
            'modelClass' => Item::class,
            'name' => 'item_ids',
        ]);

        $component->call('addOption', $item->id);
        $component->call('addOption', $item->id);

        $this->assertCount(1, $component->get('selectedIds'));
    }

    public function test_add_option_resets_search_and_closes_dropdown(): void
    {
        $item = Item::factory()->create();

        $component = Livewire::test(SearchableMultiSelect::class, [
            'modelClass' => Item::class,
            'name' => 'item_ids',
        ]);

        $component->set('search', 'something');
        $component->set('open', true);
        $component->call('addOption', $item->id);

        $this->assertEquals('', $component->get('search'));
        $this->assertFalse($component->get('open'));
    }

    // 3. removeOption: removes by id ──────────────────────────────────────────

    public function test_remove_option_removes_the_specified_id(): void
    {
        $itemA = Item::factory()->create();
        $itemB = Item::factory()->create();

        $component = Livewire::test(SearchableMultiSelect::class, [
            'modelClass' => Item::class,
            'name' => 'item_ids',
            'selectedIds' => [$itemA->id, $itemB->id],
        ]);

        $component->call('removeOption', $itemA->id);

        $ids = $component->get('selectedIds');
        $this->assertNotContains($itemA->id, $ids);
        $this->assertContains($itemB->id, $ids);
    }

    public function test_remove_option_on_non_selected_id_is_a_noop(): void
    {
        $item = Item::factory()->create();
        $other = Item::factory()->create();

        $component = Livewire::test(SearchableMultiSelect::class, [
            'modelClass' => Item::class,
            'name' => 'item_ids',
            'selectedIds' => [$item->id],
        ]);

        $component->call('removeOption', $other->id);

        $this->assertCount(1, $component->get('selectedIds'));
    }

    // 4. clear: empties the selection ─────────────────────────────────────────

    public function test_clear_empties_all_selected_ids(): void
    {
        $itemA = Item::factory()->create();
        $itemB = Item::factory()->create();

        $component = Livewire::test(SearchableMultiSelect::class, [
            'modelClass' => Item::class,
            'name' => 'item_ids',
            'selectedIds' => [$itemA->id, $itemB->id],
        ]);

        $component->call('clear');

        $this->assertEmpty($component->get('selectedIds'));
    }

    public function test_clear_resets_search_field(): void
    {
        $component = Livewire::test(SearchableMultiSelect::class, [
            'modelClass' => Item::class,
            'name' => 'item_ids',
        ]);

        $component->set('search', 'something');
        $component->call('clear');

        $this->assertEquals('', $component->get('search'));
    }

    // 5. Search composes with scopes ──────────────────────────────────────────

    public function test_search_filters_candidates_with_scope(): void
    {
        $enabledMatch = Project::factory()->withEnabled()->create(['internal_name' => 'Alpha Enabled']);
        $enabledNoMatch = Project::factory()->withEnabled()->create(['internal_name' => 'Beta Enabled']);
        $disabledMatch = Project::factory()->create(['internal_name' => 'Alpha Disabled', 'is_enabled' => false]);

        $component = Livewire::test(SearchableMultiSelect::class, [
            'modelClass' => Project::class,
            'name' => 'project_ids',
            'scopes' => 'isEnabled',
        ]);

        $component->set('search', 'Alpha');
        $component->set('open', true);

        $options = $component->get('options');

        $this->assertTrue($options->contains('id', $enabledMatch->id));
        $this->assertFalse($options->contains('id', $enabledNoMatch->id));
        $this->assertFalse($options->contains('id', $disabledMatch->id));
    }

    // 6. Ceiling guard still applies with staticOptions ───────────────────────

    public function test_static_options_ceiling_guard_throws_when_exceeded(): void
    {
        $options = array_map(
            fn ($i) => ['id' => (string) $i, 'internal_name' => "Option {$i}"],
            range(1, 51)
        );

        try {
            Livewire::test(SearchableMultiSelect::class, [
                'staticOptions' => $options,
                'name' => 'test_field',
            ]);
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (\Throwable $e) {
            $root = $this->getRootException($e);
            $this->assertInstanceOf(\InvalidArgumentException::class, $root);
            $this->assertStringContainsString('SearchableMultiSelect received', $root->getMessage());
        }
    }

    public function test_static_options_within_ceiling_mounts_successfully(): void
    {
        $options = array_map(
            fn ($i) => ['id' => (string) $i, 'internal_name' => "Option {$i}"],
            range(1, 5)
        );

        Livewire::test(SearchableMultiSelect::class, [
            'staticOptions' => $options,
            'name' => 'test_field',
        ])->assertOk();
    }

    // 7. selectedOptions renders chips without issuing a new query ─────────────

    public function test_selected_options_returns_empty_collection_when_nothing_selected(): void
    {
        $component = Livewire::test(SearchableMultiSelect::class, [
            'modelClass' => Item::class,
            'name' => 'item_ids',
        ]);

        $this->assertTrue($component->get('selectedOptions')->isEmpty());
    }

    public function test_selected_options_hydrates_from_selected_ids(): void
    {
        $itemA = Item::factory()->create(['internal_name' => 'Item A']);
        $itemB = Item::factory()->create(['internal_name' => 'Item B']);
        $other = Item::factory()->create(['internal_name' => 'Other']);

        $component = Livewire::test(SearchableMultiSelect::class, [
            'modelClass' => Item::class,
            'name' => 'item_ids',
            'selectedIds' => [$itemA->id, $itemB->id],
        ]);

        $selected = $component->get('selectedOptions');

        $this->assertCount(2, $selected);
        $this->assertTrue($selected->contains('id', $itemA->id));
        $this->assertTrue($selected->contains('id', $itemB->id));
        $this->assertFalse($selected->contains('id', $other->id));
    }

    public function test_selected_options_with_static_options_returns_matching_entries(): void
    {
        $options = [
            ['id' => '1', 'internal_name' => 'Option A'],
            ['id' => '2', 'internal_name' => 'Option B'],
            ['id' => '3', 'internal_name' => 'Option C'],
        ];

        $component = Livewire::test(SearchableMultiSelect::class, [
            'staticOptions' => $options,
            'name' => 'test_field',
            'selectedIds' => ['1', '3'],
        ]);

        $selected = $component->get('selectedOptions');

        $this->assertCount(2, $selected);
    }

    // Candidate options exclude already-selected ids ──────────────────────────

    public function test_options_excludes_already_selected_ids(): void
    {
        $itemA = Item::factory()->create(['internal_name' => 'Item A']);
        $itemB = Item::factory()->create(['internal_name' => 'Item B']);

        $component = Livewire::test(SearchableMultiSelect::class, [
            'modelClass' => Item::class,
            'name' => 'item_ids',
            'selectedIds' => [$itemA->id],
        ]);

        $component->set('open', true);

        $options = $component->get('options');

        $this->assertFalse($options->contains('id', $itemA->id));
        $this->assertTrue($options->contains('id', $itemB->id));
    }
}

<?php

namespace Tests\Unit\Support\Web\Lists;

use App\Http\Requests\Web\IndexListRequest;
use App\Models\Country;
use App\Support\Web\Lists\ListDefinition;
use App\Support\Web\Lists\ListQueryParameters;
use App\Support\Web\Lists\ListSortDefinition;
use App\Support\Web\Lists\ListState;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ListInfrastructureTest extends TestCase
{
    use RefreshDatabase;

    public function test_definition_can_apply_multi_column_search(): void
    {
        Country::factory()->create(['id' => 'FRA', 'internal_name' => 'France']);
        Country::factory()->create(['id' => 'DEU', 'internal_name' => 'Germany']);

        $query = Country::query();
        (new TestSearchListDefinition)->applySearch($query, 'FRA');

        $this->assertSame(['France'], $query->pluck('internal_name')->all());

        $query = Country::query();
        (new TestSearchListDefinition)->applySearch($query, 'Ger');

        $this->assertSame(['Germany'], $query->pluck('internal_name')->all());
    }

    public function test_required_filter_parameters_are_enforced_from_the_definition(): void
    {
        Country::factory()->create(['id' => 'FRA', 'internal_name' => 'France']);

        $request = new TestScopedIndexRequest;

        $missingParentValidator = Validator::make([], $request->rules());

        $this->assertTrue($missingParentValidator->fails());
        $this->assertArrayHasKey('country_id', $missingParentValidator->errors()->toArray());

        $validValidator = Validator::make([
            'country_id' => 'FRA',
            'sort' => 'language',
            'direction' => 'asc',
        ], $request->rules());

        $validValidator->validate();

        $this->assertFalse($validValidator->errors()->any());
    }

    public function test_list_state_exposes_filter_helpers_without_changing_query_output(): void
    {
        $state = new ListState(
            search: 'Temple',
            sort: 'updated_at',
            direction: 'desc',
            page: 3,
            perPage: 25,
            filters: [
                'country_id' => 'FRA',
                'language_id' => 'eng',
            ],
        );

        $this->assertTrue($state->hasFilter('country_id'));
        $this->assertSame('FRA', $state->filter('country_id'));
        $this->assertSame('fallback', $state->filter('missing', 'fallback'));

        $this->assertSame([
            'language_id' => 'eng',
            'q' => 'Temple',
            'sort' => 'updated_at',
            'direction' => 'desc',
            'page' => 3,
            'per_page' => 25,
        ], $state->query(['country_id']));
    }

    public function test_definition_can_resolve_related_sort_columns(): void
    {
        $definition = new TestScopedListDefinition;

        $this->assertSame('languages.internal_name', $definition->sortColumn('language'));
        $this->assertSame('created_at', $definition->sortColumn('missing-sort'));
        $this->assertSame(ListQueryParameters::DESC, $definition->sortDefinition()->defaultDirection);
    }
}

final class TestSearchListDefinition extends ListDefinition
{
    public function filterParameters(): array
    {
        return [];
    }

    public function filterRules(): array
    {
        return [];
    }

    public function sorts(): array
    {
        return [
            'created_at' => new ListSortDefinition('created_at', ListQueryParameters::DESC),
        ];
    }

    public function searchColumns(): array
    {
        return ['countries.id', 'countries.internal_name'];
    }
}

final class TestScopedListDefinition extends ListDefinition
{
    public function filterParameters(): array
    {
        return ['country_id'];
    }

    public function requiredFilterParameters(): array
    {
        return ['country_id'];
    }

    public function filterRules(): array
    {
        return [
            'country_id' => ['sometimes', 'nullable', 'string', 'size:3', 'exists:countries,id'],
        ];
    }

    public function sorts(): array
    {
        return [
            'created_at' => new ListSortDefinition('created_at', ListQueryParameters::DESC),
            'language' => new ListSortDefinition('languages.internal_name', ListQueryParameters::ASC),
        ];
    }
}

final class TestScopedIndexRequest extends IndexListRequest
{
    protected function createDefinition(): ListDefinition
    {
        return new TestScopedListDefinition;
    }
}

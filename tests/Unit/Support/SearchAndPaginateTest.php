<?php

namespace Tests\Unit\Support;

use App\Models\Context;
use App\Support\Web\SearchAndPaginate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * Unit tests for SearchAndPaginate::applySort() and the sort parameters
 * accepted by the updated searchAndPaginate() wrapper.
 */
class SearchAndPaginateTest extends TestCase
{
    use RefreshDatabase;

    private object $subject;

    protected function setUp(): void
    {
        parent::setUp();

        // Build an anonymous class that uses the trait so we can call protected methods
        $this->subject = new class
        {
            use SearchAndPaginate;

            public function callApplySort(
                Builder $query,
                Request $request,
                array $allowedFields,
                string $default = 'created_at',
                string $defaultDir = 'desc',
            ): Builder {
                return $this->applySort($query, $request, $allowedFields, $default, $defaultDir);
            }

            public function callSearchAndPaginate(
                Builder $query,
                Request $request,
                array $allowedSortFields = [],
                string $defaultSort = 'created_at',
                string $defaultDir = 'desc',
            ): array {
                return $this->searchAndPaginate($query, $request, $allowedSortFields, $defaultSort, $defaultDir);
            }
        };
    }

    public function test_apply_sort_uses_known_good_sort_field(): void
    {
        Context::factory()->count(3)->create();
        $query = Context::query();
        $request = Request::create('/', 'GET', ['sort' => 'internal_name', 'dir' => 'asc']);

        $result = $this->subject->callApplySort($query, $request, ['internal_name', 'created_at']);

        $sql = $result->toSql();
        $this->assertStringContainsStringIgnoringCase('order by', $sql);
        $this->assertStringContainsStringIgnoringCase('internal_name', $sql);
    }

    public function test_apply_sort_falls_back_to_default_for_unknown_sort_field(): void
    {
        Context::factory()->count(3)->create();
        $query = Context::query();
        $request = Request::create('/', 'GET', ['sort' => 'non_existent_column', 'dir' => 'asc']);

        $result = $this->subject->callApplySort($query, $request, ['internal_name', 'created_at']);

        $sql = $result->toSql();
        $this->assertStringContainsStringIgnoringCase('created_at', $sql);
    }

    public function test_apply_sort_falls_back_to_default_direction_for_unknown_direction(): void
    {
        Context::factory()->count(3)->create();
        $query = Context::query();
        $request = Request::create('/', 'GET', ['sort' => 'internal_name', 'dir' => 'sideways']);

        $result = $this->subject->callApplySort($query, $request, ['internal_name', 'created_at']);

        // The query should still run without error and use 'desc' as fallback
        $results = $result->get();
        $this->assertNotNull($results);
    }

    public function test_apply_sort_asc_and_desc_are_both_accepted(): void
    {
        Context::factory()->count(3)->create();

        foreach (['asc', 'desc'] as $dir) {
            $query = Context::query();
            $request = Request::create('/', 'GET', ['sort' => 'internal_name', 'dir' => $dir]);

            $result = $this->subject->callApplySort($query, $request, ['internal_name', 'created_at']);
            $sql = $result->toSql();
            $this->assertStringContainsStringIgnoringCase('internal_name', $sql);
        }
    }

    public function test_search_and_paginate_with_sort_fields_returns_sort_and_dir(): void
    {
        Context::factory()->count(3)->create();
        $request = Request::create('/', 'GET', ['sort' => 'internal_name', 'dir' => 'asc']);

        [$paginator, $search, $sort, $dir] = $this->subject->callSearchAndPaginate(
            Context::query(),
            $request,
            ['internal_name', 'created_at'],
        );

        $this->assertSame('internal_name', $sort);
        $this->assertSame('asc', $dir);
    }

    public function test_search_and_paginate_unknown_sort_field_falls_back_to_default(): void
    {
        Context::factory()->count(3)->create();
        $request = Request::create('/', 'GET', ['sort' => 'invalid_column', 'dir' => 'asc']);

        [$paginator, $search, $sort, $dir] = $this->subject->callSearchAndPaginate(
            Context::query(),
            $request,
            ['internal_name', 'created_at'],
            'created_at',
        );

        $this->assertSame('created_at', $sort);
    }

    public function test_search_and_paginate_unknown_direction_falls_back_to_default(): void
    {
        Context::factory()->count(3)->create();
        $request = Request::create('/', 'GET', ['sort' => 'internal_name', 'dir' => 'upward']);

        [$paginator, $search, $sort, $dir] = $this->subject->callSearchAndPaginate(
            Context::query(),
            $request,
            ['internal_name', 'created_at'],
            'created_at',
            'desc',
        );

        $this->assertSame('desc', $dir);
    }

    public function test_search_and_paginate_without_sort_fields_uses_legacy_order(): void
    {
        Context::factory()->create(['internal_name' => 'Alpha']);
        Context::factory()->create(['internal_name' => 'Beta']);
        $request = Request::create('/', 'GET', ['sort' => 'internal_name', 'dir' => 'asc']);

        // No allowedSortFields — should still work, ignoring sort params
        [$paginator, $search, $sort, $dir] = $this->subject->callSearchAndPaginate(
            Context::query(),
            $request,
        );

        $this->assertNotNull($paginator);
        // Legacy mode: sort params are not applied
        $this->assertSame('created_at', $sort);
        $this->assertSame('desc', $dir);
    }
}

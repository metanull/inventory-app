<?php

namespace Tests\Web\Traits;

/**
 * Reusable HTTP-based test methods for index page filtering and sorting.
 * Uses the route name provided by the concrete test class to build requests.
 */
trait TestsWebIndexFiltering
{
    /**
     * Route name for the entity's index page (e.g., 'items.index').
     */
    abstract protected function indexRouteName(): string;

    /**
     * Create a record that should be visible in an unfiltered search.
     * Returns an associative array with at least 'internal_name' key.
     */
    abstract protected function createMatchingRecord(): array;

    public function test_index_unknown_sort_field_is_ignored_and_page_still_loads(): void
    {
        $response = $this->get(route($this->indexRouteName(), ['sort' => 'nonexistent_column']));

        $response->assertOk();
    }

    public function test_index_sort_asc_is_accepted(): void
    {
        $response = $this->get(route($this->indexRouteName(), ['sort' => 'internal_name', 'dir' => 'asc']));

        $response->assertOk();
    }

    public function test_index_sort_desc_is_accepted(): void
    {
        $response = $this->get(route($this->indexRouteName(), ['sort' => 'internal_name', 'dir' => 'desc']));

        $response->assertOk();
    }

    public function test_index_unknown_sort_direction_is_ignored(): void
    {
        $response = $this->get(route($this->indexRouteName(), ['sort' => 'internal_name', 'dir' => 'sideways']));

        $response->assertOk();
    }

    public function test_index_search_query_parameter_is_accepted(): void
    {
        $record = $this->createMatchingRecord();

        $response = $this->get(route($this->indexRouteName(), ['q' => $record['internal_name']]));

        $response->assertOk()
            ->assertSee($record['internal_name']);
    }
}

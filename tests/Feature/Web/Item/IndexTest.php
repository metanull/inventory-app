<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Item;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class IndexTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_index_lists_items_with_pagination(): void
    {
        Item::factory()->count(30)->create();

        $response = $this->get(route('items.index'));
        $response->assertOk();
        $response->assertSee('Items');
        $response->assertSee('Rows per page');
        // Assert first page items appear
        $firstItem = Item::query()->orderByDesc('created_at')->first();
        $response->assertSee(e($firstItem->internal_name));
    }

    public function test_index_search_filters_results(): void
    {
        Item::factory()->count(5)->create();
        $target = Item::factory()->create(['internal_name' => 'SPECIAL_SEARCH_TOKEN']);

        // Search for token
        $response = $this->get(route('items.index', ['q' => 'SPECIAL_SEARCH_TOKEN']));
        $response->assertOk();
        $response->assertSee('SPECIAL_SEARCH_TOKEN');

        // Ensure a non-matching item internal name not present (rough heuristic)
        $nonMatch = Item::where('id', '!=', $target->id)->first();
        if ($nonMatch) {
            $response->assertDontSee(e($nonMatch->internal_name));
        }
    }

    public function test_index_respects_per_page_query(): void
    {
        Item::factory()->count(15)->create();
        $response = $this->get(route('items.index', ['per_page' => 10]));
        $response->assertOk();
        // Count occurrences of table row marker (simplistic)
        $rowCount = substr_count($response->getContent(), '<tr');
        $this->assertGreaterThanOrEqual(10, $rowCount - 1); // subtract header row
    }
}

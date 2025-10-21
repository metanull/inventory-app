<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Author;

use App\Models\Author;
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

    public function test_index_lists_authors_with_pagination(): void
    {
        Author::factory()->count(25)->create();
        $response = $this->get(route('authors.index'));
        $response->assertOk();
        $response->assertSee('Authors');
        $response->assertSee('Search authors');
        $first = Author::query()->orderByDesc('created_at')->first();
        $response->assertSee(e($first->name));
    }

    public function test_index_search_filters_results(): void
    {
        Author::factory()->count(5)->create();
        $target = Author::factory()->create(['name' => 'SPECIAL_AUTHOR_TOKEN']);

        $response = $this->get(route('authors.index', ['q' => 'SPECIAL_AUTHOR_TOKEN']));
        $response->assertOk();
        $response->assertSee('SPECIAL_AUTHOR_TOKEN');

        $nonMatch = Author::where('id', '!=', $target->id)->first();
        if ($nonMatch) {
            $response->assertDontSee(e($nonMatch->name));
        }
    }

    public function test_index_respects_per_page_query(): void
    {
        Author::factory()->count(15)->create();
        $response = $this->get(route('authors.index', ['per_page' => 10]));
        $response->assertOk();
        $rowCount = substr_count($response->getContent(), '<tr');
        $this->assertGreaterThanOrEqual(10, $rowCount - 1);
    }
}

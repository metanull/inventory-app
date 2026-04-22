<?php

namespace Tests\Web\Pages;

use App\Models\Author;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class AuthorIndexTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_index_renders_request_driven_author_page_without_livewire_markup(): void
    {
        Author::factory()->create(['name' => 'Jane Author']);

        $response = $this->get(route('authors.index'));

        $response
            ->assertOk()
            ->assertViewIs('authors.index')
            ->assertSee('Jane Author');

        $this->assertStringNotContainsString('livewire:dynamic-component', $response->getContent());
        $this->assertStringNotContainsString('wire:click', $response->getContent());
    }

    public function test_index_can_search_by_internal_name(): void
    {
        Author::factory()->create(['name' => 'Primary', 'internal_name' => 'Temple Writer']);
        Author::factory()->create(['name' => 'Other', 'internal_name' => 'Other Writer']);

        $response = $this->get(route('authors.index', ['q' => 'Temple']));

        $response
            ->assertOk()
            ->assertSee('Primary')
            ->assertDontSee('Other');
    }

    public function test_index_normalizes_non_whitelisted_sort_columns_to_the_default(): void
    {
        $response = $this->get(route('authors.index', ['sort' => 'firstname']));

        $response
            ->assertOk()
            ->assertViewHas('listState', fn ($listState): bool => $listState->sort === 'name');
    }

    public function test_index_preserves_query_strings_in_pagination_and_sort_links(): void
    {
        foreach (range(1, 11) as $index) {
            Author::factory()->create([
                'name' => 'Author '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'internal_name' => 'Temple Author '.$index,
            ]);
        }

        $response = $this->get(route('authors.index', [
            'q' => 'Temple',
            'per_page' => 10,
            'sort' => 'name',
            'direction' => 'asc',
        ]));

        $response->assertOk();

        $paginator = $response->viewData('authors');
        $nextPageUrl = $paginator->nextPageUrl();

        $this->assertNotNull($nextPageUrl);
        $this->assertStringContainsString('q=Temple', $nextPageUrl);
        $this->assertStringContainsString('per_page=10', $nextPageUrl);
        $this->assertStringContainsString(
            'href="http://localhost/web/authors?q=Temple&amp;per_page=10&amp;sort=created_at&amp;direction=asc&amp;page=1"',
            $response->getContent(),
        );
    }

    public function test_index_requires_view_data_permission(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('authors.index'));

        $response->assertForbidden();
    }
}

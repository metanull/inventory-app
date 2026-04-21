<?php

namespace Tests\Web\Pages;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class TagIndexTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_index_renders_request_driven_tag_page_without_livewire_markup(): void
    {
        Tag::factory()->create(['internal_name' => 'Temple Tag']);

        $response = $this->get(route('tags.index'));

        $response
            ->assertOk()
            ->assertViewIs('tags.index')
            ->assertSee('Temple Tag');

        $this->assertStringNotContainsString('livewire:dynamic-component', $response->getContent());
        $this->assertStringNotContainsString('wire:click', $response->getContent());
    }

    public function test_index_can_search_by_description(): void
    {
        Tag::factory()->create(['internal_name' => 'Architecture', 'description' => 'Temple decoration']);
        Tag::factory()->create(['internal_name' => 'Other Tag', 'description' => 'Other text']);

        $response = $this->get(route('tags.index', ['q' => 'Temple']));

        $response
            ->assertOk()
            ->assertSee('Architecture')
            ->assertDontSee('Other Tag');
    }

    public function test_index_normalizes_non_whitelisted_sort_columns_to_the_default(): void
    {
        $response = $this->get(route('tags.index', ['sort' => 'category']));

        $response
            ->assertOk()
            ->assertViewHas('listState', fn ($listState): bool => $listState->sort === 'internal_name');
    }

    public function test_index_preserves_query_strings_in_pagination_and_sort_links(): void
    {
        foreach (range(1, 11) as $index) {
            Tag::factory()->create([
                'internal_name' => 'Tag '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'description' => 'Temple tag '.$index,
            ]);
        }

        $response = $this->get(route('tags.index', [
            'q' => 'Temple',
            'per_page' => 10,
            'sort' => 'internal_name',
            'direction' => 'asc',
        ]));

        $response->assertOk();

        $paginator = $response->viewData('tags');
        $nextPageUrl = $paginator->nextPageUrl();

        $this->assertNotNull($nextPageUrl);
        $this->assertStringContainsString('q=Temple', $nextPageUrl);
        $this->assertStringContainsString('per_page=10', $nextPageUrl);
        $this->assertStringContainsString(
            'href="http://localhost/web/tags?q=Temple&amp;per_page=10&amp;sort=created_at&amp;direction=asc&amp;page=1"',
            $response->getContent(),
        );
    }

    public function test_index_requires_view_data_permission(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('tags.index'));

        $response->assertForbidden();
    }
}

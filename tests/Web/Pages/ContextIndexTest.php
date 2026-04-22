<?php

namespace Tests\Web\Pages;

use App\Models\Context;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class ContextIndexTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_index_renders_request_driven_context_page_without_livewire_markup(): void
    {
        Context::factory()->create(['internal_name' => 'Temple Context']);

        $response = $this->get(route('contexts.index'));

        $response
            ->assertOk()
            ->assertViewIs('contexts.index')
            ->assertSee('Temple Context');

        $this->assertStringNotContainsString('livewire:dynamic-component', $response->getContent());
        $this->assertStringNotContainsString('wire:click', $response->getContent());
    }

    public function test_index_can_search_by_internal_name(): void
    {
        Context::factory()->create(['internal_name' => 'Temple Context']);
        Context::factory()->create(['internal_name' => 'Other Context']);

        $response = $this->get(route('contexts.index', ['q' => 'Temple']));

        $response
            ->assertOk()
            ->assertSee('Temple Context')
            ->assertDontSee('Other Context');
    }

    public function test_index_normalizes_non_whitelisted_sort_columns_to_the_default(): void
    {
        $response = $this->get(route('contexts.index', ['sort' => 'backward_compatibility']));

        $response
            ->assertOk()
            ->assertViewHas('listState', fn ($listState): bool => $listState->sort === 'internal_name');
    }

    public function test_index_preserves_query_strings_in_pagination_and_sort_links(): void
    {
        foreach (range(1, 11) as $index) {
            Context::factory()->create([
                'internal_name' => 'Temple '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            ]);
        }

        $response = $this->get(route('contexts.index', [
            'q' => 'Temple',
            'per_page' => 10,
            'sort' => 'internal_name',
            'direction' => 'asc',
        ]));

        $response->assertOk();

        $paginator = $response->viewData('contexts');
        $nextPageUrl = $paginator->nextPageUrl();

        $this->assertNotNull($nextPageUrl);
        $this->assertStringContainsString('q=Temple', $nextPageUrl);
        $this->assertStringContainsString('per_page=10', $nextPageUrl);
        $this->assertStringContainsString(
            'href="http://localhost/web/contexts?q=Temple&amp;per_page=10&amp;sort=created_at&amp;direction=asc&amp;page=1"',
            $response->getContent(),
        );
    }

    public function test_index_requires_view_data_permission(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('contexts.index'));

        $response->assertForbidden();
    }
}

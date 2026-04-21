<?php

namespace Tests\Web\Pages;

use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class LanguageIndexTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_index_renders_request_driven_language_page_without_livewire_markup(): void
    {
        Language::factory()->create(['id' => 'eng', 'internal_name' => 'English']);

        $response = $this->get(route('languages.index'));

        $response
            ->assertOk()
            ->assertViewIs('languages.index')
            ->assertSee('English')
            ->assertSee('eng');

        $this->assertStringNotContainsString('livewire:dynamic-component', $response->getContent());
        $this->assertStringNotContainsString('wire:click', $response->getContent());
    }

    public function test_index_can_search_by_iso_code(): void
    {
        Language::factory()->create(['id' => 'eng', 'internal_name' => 'English']);
        Language::factory()->create(['id' => 'ara', 'internal_name' => 'Arabic']);

        $response = $this->get(route('languages.index', ['q' => 'eng']));

        $response
            ->assertOk()
            ->assertSee('English')
            ->assertDontSee('Arabic');
    }

    public function test_index_normalizes_non_whitelisted_sort_columns_to_the_default(): void
    {
        $response = $this->get(route('languages.index', ['sort' => 'backward_compatibility']));

        $response
            ->assertOk()
            ->assertViewHas('listState', fn ($listState): bool => $listState->sort === 'id');
    }

    public function test_index_preserves_query_strings_in_pagination_and_sort_links(): void
    {
        foreach (range(1, 11) as $index) {
            Language::factory()->create([
                'id' => 'l'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'internal_name' => 'Temple Language '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            ]);
        }

        $response = $this->get(route('languages.index', [
            'q' => 'Temple',
            'per_page' => 10,
            'sort' => 'id',
            'direction' => 'asc',
        ]));

        $response->assertOk();

        $paginator = $response->viewData('languages');
        $nextPageUrl = $paginator->nextPageUrl();

        $this->assertNotNull($nextPageUrl);
        $this->assertStringContainsString('q=Temple', $nextPageUrl);
        $this->assertStringContainsString('per_page=10', $nextPageUrl);
        $this->assertStringContainsString(
            'href="http://localhost/web/languages?q=Temple&amp;per_page=10&amp;sort=internal_name&amp;direction=asc&amp;page=1"',
            $response->getContent(),
        );
    }

    public function test_index_requires_view_data_permission(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('languages.index'));

        $response->assertForbidden();
    }
}

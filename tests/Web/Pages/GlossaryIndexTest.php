<?php

namespace Tests\Web\Pages;

use App\Models\Glossary;
use App\Models\GlossarySpelling;
use App\Models\GlossaryTranslation;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class GlossaryIndexTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_index_renders_request_driven_glossary_page_without_livewire_markup(): void
    {
        Language::factory()->create(['id' => 'eng', 'internal_name' => 'English']);
        $glossary = Glossary::factory()->create(['internal_name' => 'Temple Term']);
        GlossaryTranslation::factory()->create(['glossary_id' => $glossary->id, 'language_id' => 'eng']);
        GlossarySpelling::factory()->create(['glossary_id' => $glossary->id, 'language_id' => 'eng']);

        $response = $this->get(route('glossaries.index'));

        $response
            ->assertOk()
            ->assertViewIs('glossaries.index')
            ->assertSee('Temple Term')
            ->assertSee('eng');

        $this->assertStringNotContainsString('livewire:dynamic-component', $response->getContent());
        $this->assertStringNotContainsString('wire:click', $response->getContent());
    }

    public function test_index_can_search_by_internal_name(): void
    {
        Glossary::factory()->create(['internal_name' => 'Temple Term']);
        Glossary::factory()->create(['internal_name' => 'Other Term']);

        $response = $this->get(route('glossaries.index', ['q' => 'Temple']));

        $response
            ->assertOk()
            ->assertSee('Temple Term')
            ->assertDontSee('Other Term');
    }

    public function test_index_normalizes_non_whitelisted_sort_columns_to_the_default(): void
    {
        $response = $this->get(route('glossaries.index', ['sort' => 'definition']));

        $response
            ->assertOk()
            ->assertViewHas('listState', fn ($listState): bool => $listState->sort === 'internal_name');
    }

    public function test_index_preserves_query_strings_in_pagination_and_sort_links(): void
    {
        Language::factory()->create(['id' => 'eng', 'internal_name' => 'English']);

        foreach (range(1, 11) as $index) {
            $glossary = Glossary::factory()->create([
                'internal_name' => 'Temple Term '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            ]);
            GlossaryTranslation::factory()->create(['glossary_id' => $glossary->id, 'language_id' => 'eng']);
        }

        $response = $this->get(route('glossaries.index', [
            'q' => 'Temple',
            'per_page' => 10,
            'sort' => 'internal_name',
            'direction' => 'asc',
        ]));

        $response->assertOk();

        $paginator = $response->viewData('glossaries');
        $nextPageUrl = $paginator->nextPageUrl();

        $this->assertNotNull($nextPageUrl);
        $this->assertStringContainsString('q=Temple', $nextPageUrl);
        $this->assertStringContainsString('per_page=10', $nextPageUrl);
        $this->assertStringContainsString(
            'href="http://localhost/web/glossaries?q=Temple&amp;per_page=10&amp;sort=created_at&amp;direction=asc&amp;page=1"',
            $response->getContent(),
        );
    }

    public function test_index_requires_view_data_permission(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('glossaries.index'));

        $response->assertForbidden();
    }
}

<?php

namespace Tests\Web\Pages;

use App\Models\Glossary;
use App\Models\GlossarySpelling;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class GlossarySpellingIndexTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_index_renders_request_driven_page_without_livewire_markup(): void
    {
        $glossary = Glossary::factory()->create(['internal_name' => 'Test Glossary']);
        GlossarySpelling::factory()->for($glossary)->create(['spelling' => 'artefact']);

        $response = $this->get(route('glossaries.spellings.index', $glossary));

        $response
            ->assertOk()
            ->assertViewIs('glossary-spelling.index')
            ->assertSee('artefact');

        $this->assertStringNotContainsString('livewire:dynamic-component', $response->getContent());
        $this->assertStringNotContainsString('wire:click', $response->getContent());
    }

    public function test_index_scopes_spellings_to_parent_glossary(): void
    {
        $glossary = Glossary::factory()->create(['internal_name' => 'My Glossary']);
        $otherGlossary = Glossary::factory()->create(['internal_name' => 'Other Glossary']);

        GlossarySpelling::factory()->for($glossary)->create(['spelling' => 'matching-spelling']);
        GlossarySpelling::factory()->for($otherGlossary)->create(['spelling' => 'other-spelling']);

        $response = $this->get(route('glossaries.spellings.index', $glossary));

        $response
            ->assertOk()
            ->assertSee('matching-spelling')
            ->assertDontSee('other-spelling');
    }

    public function test_index_returns_not_found_for_non_existent_glossary(): void
    {
        $response = $this->get(route('glossaries.spellings.index', ['glossary' => 'non-existent-uuid']));

        $response->assertNotFound();
    }

    public function test_index_can_search_by_spelling(): void
    {
        $glossary = Glossary::factory()->create();

        GlossarySpelling::factory()->for($glossary)->create(['spelling' => 'artefact']);
        GlossarySpelling::factory()->for($glossary)->create(['spelling' => 'artifact']);

        $response = $this->get(route('glossaries.spellings.index', [
            'glossary' => $glossary,
            'q' => 'artefact',
        ]));

        $response
            ->assertOk()
            ->assertSee('artefact')
            ->assertDontSee('artifact');
    }

    public function test_index_can_sort_by_spelling(): void
    {
        $glossary = Glossary::factory()->create();

        GlossarySpelling::factory()->for($glossary)->create(['spelling' => 'zebra']);
        GlossarySpelling::factory()->for($glossary)->create(['spelling' => 'alpha']);

        $response = $this->get(route('glossaries.spellings.index', [
            'glossary' => $glossary,
            'sort' => 'spelling',
            'direction' => 'asc',
        ]));

        $response
            ->assertOk()
            ->assertSeeInOrder(['alpha', 'zebra']);
    }

    public function test_index_rejects_invalid_sort_field_gracefully(): void
    {
        $glossary = Glossary::factory()->create();

        $response = $this->get(route('glossaries.spellings.index', [
            'glossary' => $glossary,
            'sort' => 'invalid_field',
        ]));

        $response->assertOk();
    }

    public function test_index_requires_view_data_permission(): void
    {
        $glossary = Glossary::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('glossaries.spellings.index', $glossary));

        $response->assertForbidden();
    }

    public function test_index_passes_parent_glossary_to_view(): void
    {
        $glossary = Glossary::factory()->create(['internal_name' => 'My Special Glossary']);

        $response = $this->get(route('glossaries.spellings.index', $glossary));

        $response
            ->assertOk()
            ->assertSee('My Special Glossary');

        $this->assertSame($glossary->id, $response->viewData('glossary')->id);
    }

    public function test_index_preserves_query_strings_in_pagination_links(): void
    {
        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create();

        foreach (range(1, 11) as $index) {
            GlossarySpelling::factory()->for($glossary)->for($language)->create([
                'spelling' => 'spelling-'.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
            ]);
        }

        $response = $this->get(route('glossaries.spellings.index', [
            'glossary' => $glossary,
            'per_page' => 10,
            'sort' => 'spelling',
            'direction' => 'asc',
        ]));

        $response->assertOk();

        $paginator = $response->viewData('spellings');
        $nextPageUrl = $paginator->nextPageUrl();

        $this->assertNotNull($nextPageUrl);
        $this->assertStringContainsString('per_page=10', $nextPageUrl);
    }
}

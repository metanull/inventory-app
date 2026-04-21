<?php

namespace Tests\Web\Pages;

use App\Models\Glossary;
use App\Models\GlossaryTranslation;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class GlossaryTranslationIndexTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_index_renders_request_driven_page_without_livewire_markup(): void
    {
        $glossary = Glossary::factory()->create(['internal_name' => 'Test Glossary']);
        $language = Language::factory()->create(['internal_name' => 'English']);
        GlossaryTranslation::factory()->for($glossary)->for($language)->create(['definition' => 'A test definition']);

        $response = $this->get(route('glossaries.translations.index', $glossary));

        $response
            ->assertOk()
            ->assertViewIs('glossary-translation.index')
            ->assertSee('English');

        $this->assertStringNotContainsString('livewire:dynamic-component', $response->getContent());
        $this->assertStringNotContainsString('wire:click', $response->getContent());
    }

    public function test_index_scopes_translations_to_parent_glossary(): void
    {
        $glossary = Glossary::factory()->create(['internal_name' => 'My Glossary']);
        $otherGlossary = Glossary::factory()->create(['internal_name' => 'Other Glossary']);
        $language = Language::factory()->create(['internal_name' => 'English']);
        $otherLanguage = Language::factory()->create(['internal_name' => 'French']);

        GlossaryTranslation::factory()->for($glossary)->for($language)->create(['definition' => 'Matching definition']);
        GlossaryTranslation::factory()->for($otherGlossary)->for($otherLanguage)->create(['definition' => 'Other definition']);

        $response = $this->get(route('glossaries.translations.index', $glossary));

        $response
            ->assertOk()
            ->assertSee('Matching definition')
            ->assertDontSee('Other definition');
    }

    public function test_index_returns_not_found_for_non_existent_glossary(): void
    {
        $response = $this->get(route('glossaries.translations.index', ['glossary' => 'non-existent-uuid']));

        $response->assertNotFound();
    }

    public function test_index_can_filter_by_language(): void
    {
        $glossary = Glossary::factory()->create();
        $language = Language::factory()->create(['internal_name' => 'English']);
        $otherLanguage = Language::factory()->create(['internal_name' => 'French']);

        GlossaryTranslation::factory()->for($glossary)->for($language)->create(['definition' => 'English definition']);
        GlossaryTranslation::factory()->for($glossary)->for($otherLanguage)->create(['definition' => 'French definition']);

        $response = $this->get(route('glossaries.translations.index', [
            'glossary' => $glossary,
            'language' => $language->id,
        ]));

        $response
            ->assertOk()
            ->assertSee('English definition')
            ->assertDontSee('French definition');
    }

    public function test_index_can_sort_by_language_internal_name(): void
    {
        $glossary = Glossary::factory()->create();
        $languageA = Language::factory()->create(['internal_name' => 'Arabic']);
        $languageZ = Language::factory()->create(['internal_name' => 'Zulu']);

        GlossaryTranslation::factory()->for($glossary)->for($languageZ)->create();
        GlossaryTranslation::factory()->for($glossary)->for($languageA)->create();

        $response = $this->get(route('glossaries.translations.index', [
            'glossary' => $glossary,
            'sort' => 'language.internal_name',
            'direction' => 'asc',
        ]));

        $response
            ->assertOk()
            ->assertSeeInOrder(['Arabic', 'Zulu']);
    }

    public function test_index_rejects_invalid_sort_field_gracefully(): void
    {
        $glossary = Glossary::factory()->create();

        $response = $this->get(route('glossaries.translations.index', [
            'glossary' => $glossary,
            'sort' => 'invalid_field',
        ]));

        $response->assertOk();
    }

    public function test_index_requires_view_data_permission(): void
    {
        $glossary = Glossary::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('glossaries.translations.index', $glossary));

        $response->assertForbidden();
    }

    public function test_index_passes_parent_glossary_to_view(): void
    {
        $glossary = Glossary::factory()->create(['internal_name' => 'My Special Glossary']);

        $response = $this->get(route('glossaries.translations.index', $glossary));

        $response
            ->assertOk()
            ->assertSee('My Special Glossary');

        $this->assertSame($glossary->id, $response->viewData('glossary')->id);
    }

    public function test_index_passes_languages_to_view_for_dropdown(): void
    {
        $glossary = Glossary::factory()->create();
        Language::factory()->create(['internal_name' => 'FilterLanguage']);

        $response = $this->get(route('glossaries.translations.index', $glossary));

        $response->assertOk();

        $languages = $response->viewData('languages');
        $this->assertNotEmpty($languages);
        $response->assertSee('FilterLanguage');
    }

    public function test_index_preserves_query_strings_in_pagination_links(): void
    {
        $glossary = Glossary::factory()->create();

        foreach (range(1, 11) as $index) {
            $language = Language::factory()->create(['internal_name' => 'Language '.str_pad((string) $index, 2, '0', STR_PAD_LEFT)]);
            GlossaryTranslation::factory()->for($glossary)->for($language)->create();
        }

        $response = $this->get(route('glossaries.translations.index', [
            'glossary' => $glossary,
            'per_page' => 10,
            'sort' => 'language.internal_name',
            'direction' => 'asc',
        ]));

        $response->assertOk();

        $paginator = $response->viewData('translations');
        $nextPageUrl = $paginator->nextPageUrl();

        $this->assertNotNull($nextPageUrl);
        $this->assertStringContainsString('per_page=10', $nextPageUrl);
    }
}

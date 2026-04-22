<?php

namespace Tests\Web\Pages;

use App\Models\Context;
use App\Models\Language;
use App\Models\Partner;
use App\Models\PartnerTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class PartnerTranslationIndexTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_index_renders_request_driven_page_without_livewire_markup(): void
    {
        $partner = Partner::factory()->create(['internal_name' => 'Test Partner']);
        PartnerTranslation::factory()->forPartner($partner->id)->create(['name' => 'Alpha Translation']);

        $response = $this->get(route('partner-translations.index', ['partner_id' => $partner->id]));

        $response
            ->assertOk()
            ->assertViewIs('partner-translations.index')
            ->assertSee('Alpha Translation');

        $this->assertStringNotContainsString('livewire:dynamic-component', $response->getContent());
        $this->assertStringNotContainsString('wire:click', $response->getContent());
    }

    public function test_index_requires_parent_partner_id(): void
    {
        $response = $this->get(route('partner-translations.index'));

        $response->assertRedirect();
    }

    public function test_index_scopes_translations_to_parent_partner(): void
    {
        $partner = Partner::factory()->create(['internal_name' => 'My Partner']);
        $otherPartner = Partner::factory()->create(['internal_name' => 'Other Partner']);

        PartnerTranslation::factory()->forPartner($partner->id)->create(['name' => 'Matching Translation']);
        PartnerTranslation::factory()->forPartner($otherPartner->id)->create(['name' => 'Other Translation']);

        $response = $this->get(route('partner-translations.index', ['partner_id' => $partner->id]));

        $response
            ->assertOk()
            ->assertSee('Matching Translation')
            ->assertDontSee('Other Translation');
    }

    public function test_index_can_filter_by_language(): void
    {
        $partner = Partner::factory()->create();
        $language = Language::factory()->create(['internal_name' => 'English']);
        $otherLanguage = Language::factory()->create(['internal_name' => 'French']);

        PartnerTranslation::factory()->forPartner($partner->id)->forLanguage($language->id)->create(['name' => 'English Translation']);
        PartnerTranslation::factory()->forPartner($partner->id)->forLanguage($otherLanguage->id)->create(['name' => 'French Translation']);

        $response = $this->get(route('partner-translations.index', [
            'partner_id' => $partner->id,
            'language' => $language->id,
        ]));

        $response
            ->assertOk()
            ->assertSee('English Translation')
            ->assertDontSee('French Translation');
    }

    public function test_index_can_filter_by_context(): void
    {
        $partner = Partner::factory()->create();
        $context = Context::factory()->create(['internal_name' => 'Web Context']);
        $otherContext = Context::factory()->create(['internal_name' => 'Print Context']);

        PartnerTranslation::factory()->forPartner($partner->id)->forContext($context->id)->create(['name' => 'Web Translation']);
        PartnerTranslation::factory()->forPartner($partner->id)->forContext($otherContext->id)->create(['name' => 'Print Translation']);

        $response = $this->get(route('partner-translations.index', [
            'partner_id' => $partner->id,
            'context' => $context->id,
        ]));

        $response
            ->assertOk()
            ->assertSee('Web Translation')
            ->assertDontSee('Print Translation');
    }

    public function test_index_can_search_by_translation_name(): void
    {
        $partner = Partner::factory()->create();

        PartnerTranslation::factory()->forPartner($partner->id)->create(['name' => 'Museum Alpha']);
        PartnerTranslation::factory()->forPartner($partner->id)->create(['name' => 'Other Museum']);

        $response = $this->get(route('partner-translations.index', [
            'partner_id' => $partner->id,
            'q' => 'Alpha',
        ]));

        $response
            ->assertOk()
            ->assertSee('Museum Alpha')
            ->assertDontSee('Other Museum');
    }

    public function test_index_rejects_invalid_sort_field(): void
    {
        $partner = Partner::factory()->create();

        $response = $this->get(route('partner-translations.index', [
            'partner_id' => $partner->id,
            'sort' => 'invalid_field',
        ]));

        $response->assertOk();
    }

    public function test_index_can_sort_by_language_internal_name(): void
    {
        $partner = Partner::factory()->create();
        $languageA = Language::factory()->create(['internal_name' => 'Arabic']);
        $languageZ = Language::factory()->create(['internal_name' => 'Zulu']);

        PartnerTranslation::factory()->forPartner($partner->id)->forLanguage($languageZ->id)->create(['name' => 'Zulu Translation']);
        PartnerTranslation::factory()->forPartner($partner->id)->forLanguage($languageA->id)->create(['name' => 'Arabic Translation']);

        $response = $this->get(route('partner-translations.index', [
            'partner_id' => $partner->id,
            'sort' => 'language.internal_name',
            'direction' => 'asc',
        ]));

        $response
            ->assertOk()
            ->assertSeeInOrder(['Arabic Translation', 'Zulu Translation']);
    }

    public function test_index_preserves_query_strings_in_pagination_and_sort_links(): void
    {
        $partner = Partner::factory()->create(['internal_name' => 'Paginated Partner']);
        $language = Language::factory()->create(['internal_name' => 'TestLanguage']);

        foreach (range(1, 11) as $index) {
            PartnerTranslation::factory()
                ->forPartner($partner->id)
                ->forLanguage($language->id)
                ->create(['name' => 'Translation '.str_pad((string) $index, 2, '0', STR_PAD_LEFT)]);
        }

        $response = $this->get(route('partner-translations.index', [
            'partner_id' => $partner->id,
            'per_page' => 10,
            'sort' => 'language.internal_name',
            'direction' => 'asc',
        ]));

        $response->assertOk();

        $paginator = $response->viewData('partnerTranslations');
        $nextPageUrl = $paginator->nextPageUrl();

        $this->assertNotNull($nextPageUrl);
        $this->assertStringContainsString('partner_id='.$partner->id, $nextPageUrl);
        $this->assertStringContainsString('per_page=10', $nextPageUrl);
    }

    public function test_index_requires_view_data_permission(): void
    {
        $partner = Partner::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('partner-translations.index', ['partner_id' => $partner->id]));

        $response->assertForbidden();
    }

    public function test_index_passes_parent_partner_to_view(): void
    {
        $partner = Partner::factory()->create(['internal_name' => 'My Special Partner']);

        $response = $this->get(route('partner-translations.index', ['partner_id' => $partner->id]));

        $response
            ->assertOk()
            ->assertSee('My Special Partner');

        $this->assertSame($partner->id, $response->viewData('partner')->id);
    }

    public function test_index_does_not_preload_full_language_or_context_tables(): void
    {
        $partner = Partner::factory()->create();

        $response = $this->get(route('partner-translations.index', ['partner_id' => $partner->id]));

        $response->assertOk();
        $this->assertArrayNotHasKey('languages', $response->viewData());
        $this->assertArrayNotHasKey('contexts', $response->viewData());
    }

    public function test_index_exposes_selected_language_when_filter_is_active(): void
    {
        $partner = Partner::factory()->create();
        $language = Language::factory()->create(['internal_name' => 'English']);
        PartnerTranslation::factory()->forPartner($partner->id)->forLanguage($language->id)->create();

        $response = $this->get(route('partner-translations.index', [
            'partner_id' => $partner->id,
            'language' => $language->id,
        ]));

        $response->assertOk();
        $this->assertSame($language->id, $response->viewData('selectedLanguage')->id);
    }

    public function test_index_exposes_selected_context_when_filter_is_active(): void
    {
        $partner = Partner::factory()->create();
        $context = Context::factory()->create(['internal_name' => 'Web']);
        PartnerTranslation::factory()->forPartner($partner->id)->forContext($context->id)->create();

        $response = $this->get(route('partner-translations.index', [
            'partner_id' => $partner->id,
            'context' => $context->id,
        ]));

        $response->assertOk();
        $this->assertSame($context->id, $response->viewData('selectedContext')->id);
    }

    public function test_index_exposes_null_selected_options_when_no_filter_active(): void
    {
        $partner = Partner::factory()->create();

        $response = $this->get(route('partner-translations.index', ['partner_id' => $partner->id]));

        $response->assertOk();
        $this->assertNull($response->viewData('selectedLanguage'));
        $this->assertNull($response->viewData('selectedContext'));
    }
}

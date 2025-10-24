<?php

declare(strict_types=1);

namespace Tests\Feature\Web\PartnerTranslation;

use App\Models\Context;
use App\Models\Language;
use App\Models\Partner;
use App\Models\PartnerTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class IndexTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_index_lists_partner_translations_with_pagination(): void
    {
        // Create 20 translations with different partners to avoid unique constraint violation
        PartnerTranslation::factory()->count(20)->create();

        $response = $this->get(route('partner-translations.index'));
        $response->assertOk();
        $response->assertSee('Partner Translations');
        $response->assertSee('Rows per page');

        $first = PartnerTranslation::query()->orderByDesc('created_at')->first();
        $response->assertSee(e($first->name));
    }

    public function test_index_search_filters_results(): void
    {
        // Create 5 translations with different partners to avoid unique constraint violation
        PartnerTranslation::factory()->count(5)->create();

        $target = PartnerTranslation::factory()->create([
            'name' => 'SPECIAL_PARTNER_TRANSLATION_TOKEN',
        ]);

        $response = $this->get(route('partner-translations.index', ['q' => 'SPECIAL_PARTNER_TRANSLATION_TOKEN']));
        $response->assertOk();
        $response->assertSee('SPECIAL_PARTNER_TRANSLATION_TOKEN');

        $nonMatch = PartnerTranslation::where('id', '!=', $target->id)->first();
        if ($nonMatch) {
            $response->assertDontSee(e($nonMatch->name));
        }
    }

    public function test_index_can_filter_by_context(): void
    {
        $partner = Partner::factory()->create();
        $language = Language::factory()->create();
        $context1 = Context::factory()->create(['internal_name' => 'Context 1']);
        $context2 = Context::factory()->create(['internal_name' => 'Context 2']);

        $translation1 = PartnerTranslation::factory()->create([
            'partner_id' => $partner->id,
            'language_id' => $language->id,
            'context_id' => $context1->id,
            'name' => 'Translation in Context 1',
        ]);

        $translation2 = PartnerTranslation::factory()->create([
            'partner_id' => $partner->id,
            'language_id' => $language->id,
            'context_id' => $context2->id,
            'name' => 'Translation in Context 2',
        ]);

        $response = $this->get(route('partner-translations.index', ['contextFilter' => $context1->id]));
        $response->assertOk();
        $response->assertSee('Translation in Context 1');
        $response->assertDontSee('Translation in Context 2');
    }
}

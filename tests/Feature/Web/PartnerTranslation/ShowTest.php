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

class ShowTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_show_displays_partner_translation_details(): void
    {
        $partner = Partner::factory()->create(['internal_name' => 'Test Partner']);
        $language = Language::factory()->create(['internal_name' => 'English']);
        $context = Context::factory()->create(['internal_name' => 'Default Context']);

        $translation = PartnerTranslation::factory()->create([
            'partner_id' => $partner->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Translation Name',
            'description' => 'Translation description',
            'city_display' => 'Paris',
        ]);

        $response = $this->get(route('partner-translations.show', $translation));
        $response->assertOk();
        $response->assertSee('Translation Name');
        $response->assertSee('Translation description');
        $response->assertSee('Paris');
        $response->assertSee('Test Partner');
        $response->assertSee('English');
        $response->assertSee('Default Context');
    }
}

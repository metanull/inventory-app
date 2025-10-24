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

class EditTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_edit_displays_form_with_existing_data(): void
    {
        $partner = Partner::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $translation = PartnerTranslation::factory()->create([
            'partner_id' => $partner->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Existing Translation',
            'description' => 'Existing description',
        ]);

        $response = $this->get(route('partner-translations.edit', $translation));
        $response->assertOk();
        $response->assertSee('Edit Partner Translation');
        $response->assertSee('Existing Translation');
        $response->assertSee('Existing description');
    }
}

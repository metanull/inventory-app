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

class UpdateTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_update_modifies_partner_translation_and_redirects(): void
    {
        $partner = Partner::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $translation = PartnerTranslation::factory()->create([
            'partner_id' => $partner->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Original Name',
            'description' => 'Original description',
        ]);

        $payload = [
            'partner_id' => $partner->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ];

        $response = $this->put(route('partner-translations.update', $translation), $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('partner_translations', [
            'id' => $translation->id,
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ]);
    }

    public function test_update_validation_errors(): void
    {
        $partner = Partner::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $translation = PartnerTranslation::factory()->create([
            'partner_id' => $partner->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ]);

        $response = $this->put(route('partner-translations.update', $translation), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['partner_id', 'language_id', 'context_id', 'name']);
    }
}

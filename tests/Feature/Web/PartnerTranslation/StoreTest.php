<?php

declare(strict_types=1);

namespace Tests\Feature\Web\PartnerTranslation;

use App\Models\Context;
use App\Models\Language;
use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class StoreTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_store_persists_partner_translation_and_redirects(): void
    {
        $partner = Partner::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $payload = [
            'partner_id' => $partner->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Test Partner Translation',
            'description' => 'Test description',
        ];

        $response = $this->post(route('partner-translations.store'), $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('partner_translations', [
            'partner_id' => $partner->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Test Partner Translation',
            'description' => 'Test description',
        ]);
    }

    public function test_store_with_all_optional_fields(): void
    {
        $partner = Partner::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $payload = [
            'partner_id' => $partner->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Full Translation',
            'description' => 'Full description',
            'city_display' => 'Paris',
            'address_line_1' => '123 Main St',
            'address_line_2' => 'Apt 4B',
            'postal_code' => '75001',
            'address_notes' => 'Near the Louvre',
            'contact_name' => 'John Doe',
            'contact_email_general' => 'info@partner.com',
            'contact_email_press' => 'press@partner.com',
            'contact_phone' => '+33 1 23 45 67 89',
            'contact_website' => 'https://partner.com',
            'contact_notes' => 'Available Mon-Fri',
            'backward_compatibility' => 'legacy-001',
        ];

        $response = $this->post(route('partner-translations.store'), $payload);
        $response->assertRedirect();

        $this->assertDatabaseHas('partner_translations', [
            'name' => 'Full Translation',
            'city_display' => 'Paris',
            'address_line_1' => '123 Main St',
            'contact_name' => 'John Doe',
            'contact_email_general' => 'info@partner.com',
        ]);
    }

    public function test_store_validation_errors(): void
    {
        $response = $this->post(route('partner-translations.store'), [
            'name' => '',
        ]);

        $response->assertSessionHasErrors(['partner_id', 'language_id', 'context_id', 'name']);
    }

    public function test_store_enforces_unique_constraint(): void
    {
        $partner = Partner::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        // Create first translation
        $payload = [
            'partner_id' => $partner->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Test Translation',
            'description' => 'Test description',
        ];

        $this->post(route('partner-translations.store'), $payload);

        // Try to create duplicate
        $response = $this->post(route('partner-translations.store'), $payload);
        $response->assertSessionHasErrors();
    }
}

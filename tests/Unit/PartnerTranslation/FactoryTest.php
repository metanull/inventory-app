<?php

namespace Tests\Unit\PartnerTranslation;

use App\Models\Context;
use App\Models\Language;
use App\Models\Partner;
use App\Models\PartnerTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_valid_partner_translation(): void
    {
        $partnerTranslation = PartnerTranslation::factory()->create();

        $this->assertDatabaseHas('partner_translations', [
            'id' => $partnerTranslation->id,
        ]);

        $this->assertInstanceOf(Partner::class, $partnerTranslation->partner);
        $this->assertInstanceOf(Language::class, $partnerTranslation->language);
        $this->assertInstanceOf(Context::class, $partnerTranslation->context);
    }

    public function test_factory_can_create_with_full_address(): void
    {
        $partnerTranslation = PartnerTranslation::factory()
            ->withFullAddress()
            ->create();

        $this->assertNotNull($partnerTranslation->city_display);
        $this->assertNotNull($partnerTranslation->address_line_1);
        $this->assertNotNull($partnerTranslation->postal_code);
    }

    public function test_factory_can_create_with_full_contact(): void
    {
        $partnerTranslation = PartnerTranslation::factory()
            ->withFullContact()
            ->create();

        $this->assertNotNull($partnerTranslation->contact_name);
        $this->assertNotNull($partnerTranslation->contact_email_general);
        $this->assertNotNull($partnerTranslation->contact_phone);
        $this->assertNotNull($partnerTranslation->contact_website);
    }

    public function test_factory_can_create_for_specific_partner(): void
    {
        $partner = Partner::factory()->create();
        $partnerTranslation = PartnerTranslation::factory()
            ->forPartner($partner->id)
            ->create();

        $this->assertEquals($partner->id, $partnerTranslation->partner_id);
    }

    public function test_factory_can_create_for_specific_language(): void
    {
        $language = Language::factory()->create();
        $partnerTranslation = PartnerTranslation::factory()
            ->forLanguage($language->id)
            ->create();

        $this->assertEquals($language->id, $partnerTranslation->language_id);
    }

    public function test_factory_can_create_for_specific_context(): void
    {
        $context = Context::factory()->create();
        $partnerTranslation = PartnerTranslation::factory()
            ->forContext($context->id)
            ->create();

        $this->assertEquals($context->id, $partnerTranslation->context_id);
    }

    public function test_factory_museum_state(): void
    {
        $partnerTranslation = PartnerTranslation::factory()
            ->museum()
            ->create();

        $this->assertStringContainsString('Museum', $partnerTranslation->name);
    }

    public function test_factory_institution_state(): void
    {
        $partnerTranslation = PartnerTranslation::factory()
            ->institution()
            ->create();

        $this->assertStringContainsString('Institute', $partnerTranslation->name);
    }
}

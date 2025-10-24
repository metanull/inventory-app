<?php

namespace Tests\Unit\PartnerTranslationImage;

use App\Models\PartnerTranslation;
use App\Models\PartnerTranslationImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_valid_partner_translation_image(): void
    {
        $partnerTranslationImage = PartnerTranslationImage::factory()->create();

        $this->assertDatabaseHas('partner_translation_images', [
            'id' => $partnerTranslationImage->id,
        ]);

        $this->assertInstanceOf(PartnerTranslation::class, $partnerTranslationImage->partnerTranslation);
        $this->assertNotNull($partnerTranslationImage->path);
        $this->assertNotNull($partnerTranslationImage->original_name);
        $this->assertNotNull($partnerTranslationImage->mime_type);
        $this->assertGreaterThan(0, $partnerTranslationImage->size);
    }

    public function test_factory_can_create_for_specific_partner_translation(): void
    {
        $partnerTranslation = PartnerTranslation::factory()->create();
        $partnerTranslationImage = PartnerTranslationImage::factory()
            ->forPartnerTranslation($partnerTranslation)
            ->create();

        $this->assertEquals($partnerTranslation->id, $partnerTranslationImage->partner_translation_id);
    }

    public function test_factory_can_create_with_specific_order(): void
    {
        $partnerTranslationImage = PartnerTranslationImage::factory()
            ->withOrder(3)
            ->create();

        $this->assertEquals(3, $partnerTranslationImage->display_order);
    }

    public function test_factory_logo_state(): void
    {
        $partnerTranslationImage = PartnerTranslationImage::factory()
            ->logo()
            ->create();

        $this->assertStringContainsString('.svg', $partnerTranslationImage->path);
        $this->assertEquals('image/svg+xml', $partnerTranslationImage->mime_type);
    }

    public function test_factory_banner_state(): void
    {
        $partnerTranslationImage = PartnerTranslationImage::factory()
            ->banner()
            ->create();

        $this->assertStringContainsString('.jpg', $partnerTranslationImage->path);
        $this->assertEquals('image/jpeg', $partnerTranslationImage->mime_type);
    }
}

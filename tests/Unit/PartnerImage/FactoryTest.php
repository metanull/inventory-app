<?php

namespace Tests\Unit\PartnerImage;

use App\Models\Partner;
use App\Models\PartnerImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_valid_partner_image(): void
    {
        $partnerImage = PartnerImage::factory()->create();

        $this->assertDatabaseHas('partner_images', [
            'id' => $partnerImage->id,
        ]);

        $this->assertInstanceOf(Partner::class, $partnerImage->partner);
        $this->assertNotNull($partnerImage->path);
        $this->assertNotNull($partnerImage->original_name);
        $this->assertNotNull($partnerImage->mime_type);
        $this->assertGreaterThan(0, $partnerImage->size);
    }

    public function test_factory_can_create_for_specific_partner(): void
    {
        $partner = Partner::factory()->create();
        $partnerImage = PartnerImage::factory()
            ->forPartner($partner)
            ->create();

        $this->assertEquals($partner->id, $partnerImage->partner_id);
    }

    public function test_factory_can_create_with_specific_order(): void
    {
        $partnerImage = PartnerImage::factory()
            ->withOrder(5)
            ->create();

        $this->assertEquals(5, $partnerImage->display_order);
    }

    public function test_factory_logo_state(): void
    {
        $partnerImage = PartnerImage::factory()
            ->logo()
            ->create();

        $this->assertStringContainsString('.svg', $partnerImage->path);
        $this->assertEquals('image/svg+xml', $partnerImage->mime_type);
    }

    public function test_factory_banner_state(): void
    {
        $partnerImage = PartnerImage::factory()
            ->banner()
            ->create();

        $this->assertStringContainsString('.jpg', $partnerImage->path);
        $this->assertEquals('image/jpeg', $partnerImage->mime_type);
    }
}

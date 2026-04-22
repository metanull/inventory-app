<?php

namespace Tests\Web\Pages;

use App\Models\AvailableImage;
use App\Models\Partner;
use App\Models\PartnerImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class PartnerImageTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_create_requires_authentication(): void
    {
        auth()->logout();
        $partner = Partner::factory()->create();

        $response = $this->get(route('partners.partner-images.create', ['partner' => $partner]));

        $response->assertRedirect(route('login'));
    }

    public function test_create_page_passes_available_images_from_controller(): void
    {
        $partner = Partner::factory()->create();
        AvailableImage::factory()->count(3)->create();

        $response = $this->get(route('partners.partner-images.create', ['partner' => $partner]));

        $response->assertOk()
            ->assertViewIs('partner-images.create')
            ->assertViewHas('availableImages')
            ->assertViewHas('partner');
    }

    public function test_edit_requires_authentication(): void
    {
        auth()->logout();
        $partner = Partner::factory()->create();
        $partnerImage = PartnerImage::factory()->forPartner($partner)->create();

        $response = $this->get(route('partners.partner-images.edit', ['partner' => $partner, 'partner_image' => $partnerImage]));

        $response->assertRedirect(route('login'));
    }

    public function test_edit_page_passes_partner_and_image_from_controller(): void
    {
        $partner = Partner::factory()->create();
        $partnerImage = PartnerImage::factory()->forPartner($partner)->create();

        $response = $this->get(route('partners.partner-images.edit', ['partner' => $partner, 'partner_image' => $partnerImage]));

        $response->assertOk()
            ->assertViewIs('partner-images.edit')
            ->assertViewHas('partner', $partner)
            ->assertViewHas('partnerImage', $partnerImage);
    }

    public function test_edit_returns_404_for_image_belonging_to_different_partner(): void
    {
        $partner = Partner::factory()->create();
        $otherPartner = Partner::factory()->create();
        $partnerImage = PartnerImage::factory()->forPartner($otherPartner)->create();

        $response = $this->get(route('partners.partner-images.edit', ['partner' => $partner, 'partner_image' => $partnerImage]));

        $response->assertNotFound();
    }
}

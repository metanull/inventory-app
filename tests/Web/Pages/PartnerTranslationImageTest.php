<?php

namespace Tests\Web\Pages;

use App\Models\AvailableImage;
use App\Models\PartnerTranslation;
use App\Models\PartnerTranslationImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class PartnerTranslationImageTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_create_requires_authentication(): void
    {
        auth()->logout();
        $partnerTranslation = PartnerTranslation::factory()->create();

        $response = $this->get(route('partner-translations.partner-translation-images.create', ['partner_translation' => $partnerTranslation]));

        $response->assertRedirect(route('login'));
    }

    public function test_create_page_passes_available_images_from_controller(): void
    {
        $partnerTranslation = PartnerTranslation::factory()->create();
        AvailableImage::factory()->count(3)->create();

        $response = $this->get(route('partner-translations.partner-translation-images.create', ['partner_translation' => $partnerTranslation]));

        $response->assertOk()
            ->assertViewIs('partner-translation-images.create')
            ->assertViewHas('availableImages')
            ->assertViewHas('partnerTranslation');
    }

    public function test_edit_requires_authentication(): void
    {
        auth()->logout();
        $partnerTranslation = PartnerTranslation::factory()->create();
        $partnerTranslationImage = PartnerTranslationImage::factory()->forPartnerTranslation($partnerTranslation)->create();

        $response = $this->get(route('partner-translations.partner-translation-images.edit', [
            'partner_translation' => $partnerTranslation,
            'partner_translation_image' => $partnerTranslationImage,
        ]));

        $response->assertRedirect(route('login'));
    }

    public function test_edit_page_passes_partner_translation_and_image_from_controller(): void
    {
        $partnerTranslation = PartnerTranslation::factory()->create();
        $partnerTranslationImage = PartnerTranslationImage::factory()->forPartnerTranslation($partnerTranslation)->create();

        $response = $this->get(route('partner-translations.partner-translation-images.edit', [
            'partner_translation' => $partnerTranslation,
            'partner_translation_image' => $partnerTranslationImage,
        ]));

        $response->assertOk()
            ->assertViewIs('partner-translation-images.edit')
            ->assertViewHas('partnerTranslation', $partnerTranslation)
            ->assertViewHas('partnerTranslationImage', $partnerTranslationImage);
    }

    public function test_edit_returns_404_for_image_belonging_to_different_partner_translation(): void
    {
        $partnerTranslation = PartnerTranslation::factory()->create();
        $otherPartnerTranslation = PartnerTranslation::factory()->create();
        $partnerTranslationImage = PartnerTranslationImage::factory()->forPartnerTranslation($otherPartnerTranslation)->create();

        $response = $this->get(route('partner-translations.partner-translation-images.edit', [
            'partner_translation' => $partnerTranslation,
            'partner_translation_image' => $partnerTranslationImage,
        ]));

        $response->assertNotFound();
    }
}

<?php

namespace Tests\Web\Pages;

use App\Models\Partner;
use App\Models\PartnerTranslation;
use App\Models\PartnerTranslationImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class PartnerTranslationImageIndexTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_index_renders_request_driven_page_without_livewire_markup(): void
    {
        $partnerTranslation = PartnerTranslation::factory()->create(['name' => 'Test Translation']);
        PartnerTranslationImage::factory()->forPartnerTranslation($partnerTranslation)->create(['original_name' => 'photo.jpg']);

        $response = $this->get(route('partner-translations.partner-translation-images.index', $partnerTranslation));

        $response
            ->assertOk()
            ->assertViewIs('partner-translation-images.index')
            ->assertSee('photo.jpg');

        $this->assertStringNotContainsString('livewire:dynamic-component', $response->getContent());
        $this->assertStringNotContainsString('wire:click', $response->getContent());
    }

    public function test_index_scopes_images_to_parent_translation(): void
    {
        $partnerTranslation = PartnerTranslation::factory()->create(['name' => 'My Translation']);
        $otherTranslation = PartnerTranslation::factory()->create(['name' => 'Other Translation']);

        PartnerTranslationImage::factory()->forPartnerTranslation($partnerTranslation)->create(['original_name' => 'matching-image.jpg']);
        PartnerTranslationImage::factory()->forPartnerTranslation($otherTranslation)->create(['original_name' => 'other-image.jpg']);

        $response = $this->get(route('partner-translations.partner-translation-images.index', $partnerTranslation));

        $response
            ->assertOk()
            ->assertSee('matching-image.jpg')
            ->assertDontSee('other-image.jpg');
    }

    public function test_index_returns_not_found_for_non_existent_translation(): void
    {
        $response = $this->get(route('partner-translations.partner-translation-images.index', ['partner_translation' => 'non-existent-uuid']));

        $response->assertNotFound();
    }

    public function test_index_can_search_by_original_name(): void
    {
        $partnerTranslation = PartnerTranslation::factory()->create();

        PartnerTranslationImage::factory()->forPartnerTranslation($partnerTranslation)->create(['original_name' => 'banner-photo.jpg']);
        PartnerTranslationImage::factory()->forPartnerTranslation($partnerTranslation)->create(['original_name' => 'other-photo.jpg']);

        $response = $this->get(route('partner-translations.partner-translation-images.index', [
            'partner_translation' => $partnerTranslation,
            'q' => 'banner-photo',
        ]));

        $response
            ->assertOk()
            ->assertSee('banner-photo.jpg')
            ->assertDontSee('other-photo.jpg');
    }

    public function test_index_can_sort_by_display_order(): void
    {
        $partnerTranslation = PartnerTranslation::factory()->create();

        PartnerTranslationImage::factory()->forPartnerTranslation($partnerTranslation)->withOrder(2)->create(['original_name' => 'second.jpg']);
        PartnerTranslationImage::factory()->forPartnerTranslation($partnerTranslation)->withOrder(1)->create(['original_name' => 'first.jpg']);

        $response = $this->get(route('partner-translations.partner-translation-images.index', [
            'partner_translation' => $partnerTranslation,
            'sort' => 'display_order',
            'direction' => 'asc',
        ]));

        $response
            ->assertOk()
            ->assertSeeInOrder(['first.jpg', 'second.jpg']);
    }

    public function test_index_rejects_invalid_sort_field_gracefully(): void
    {
        $partnerTranslation = PartnerTranslation::factory()->create();

        $response = $this->get(route('partner-translations.partner-translation-images.index', [
            'partner_translation' => $partnerTranslation,
            'sort' => 'invalid_field',
        ]));

        $response->assertOk();
    }

    public function test_index_requires_view_data_permission(): void
    {
        $partnerTranslation = PartnerTranslation::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('partner-translations.partner-translation-images.index', $partnerTranslation));

        $response->assertForbidden();
    }

    public function test_index_passes_parent_translation_and_partner_to_view(): void
    {
        $partner = Partner::factory()->create(['internal_name' => 'Grand Museum']);
        $partnerTranslation = PartnerTranslation::factory()->create([
            'partner_id' => $partner->id,
            'name' => 'Grand Museum EN',
        ]);

        $response = $this->get(route('partner-translations.partner-translation-images.index', $partnerTranslation));

        $response
            ->assertOk()
            ->assertSee('Grand Museum EN');

        $this->assertSame($partnerTranslation->id, $response->viewData('partnerTranslation')->id);
        $this->assertSame($partner->id, $response->viewData('partner')->id);
    }

    public function test_index_contains_upload_zone_component(): void
    {
        $partnerTranslation = PartnerTranslation::factory()->create();

        $response = $this->get(route('partner-translations.partner-translation-images.index', $partnerTranslation));

        $response
            ->assertOk()
            ->assertSee('imageUploadZone');
    }
}

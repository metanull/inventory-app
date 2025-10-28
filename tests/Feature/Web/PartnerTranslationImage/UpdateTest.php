<?php

namespace Tests\Feature\Web\PartnerTranslationImage;

use App\Models\PartnerTranslation;
use App\Models\PartnerTranslationImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class UpdateTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected PartnerTranslation $partnerTranslation;

    protected PartnerTranslationImage $partnerTranslationImage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->partnerTranslation = PartnerTranslation::factory()->create();
        $this->partnerTranslationImage = PartnerTranslationImage::factory()->for($this->partnerTranslation, 'partnerTranslation')->create();
    }

    public function test_authenticated_user_can_update_alt_text(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);

        $response = $this->actingAs($user)
            ->put(route('partner-translations.partner-translation-images.update', [$this->partnerTranslation, $this->partnerTranslationImage]), [
                'alt_text' => 'Updated Alt Text',
            ]);

        $response->assertRedirect(route('partner-translations.show', $this->partnerTranslation));
        $response->assertSessionHas('success', 'Image updated successfully');

        $this->assertDatabaseHas('partner_translation_images', [
            'id' => $this->partnerTranslationImage->id,
            'alt_text' => 'Updated Alt Text',
        ]);
    }

    public function test_alt_text_can_be_null(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);
        $this->partnerTranslationImage->update(['alt_text' => 'Some text']);

        $response = $this->actingAs($user)
            ->put(route('partner-translations.partner-translation-images.update', [$this->partnerTranslation, $this->partnerTranslationImage]), [
                'alt_text' => null,
            ]);

        $response->assertRedirect(route('partner-translations.show', $this->partnerTranslation));

        $this->assertDatabaseHas('partner_translation_images', [
            'id' => $this->partnerTranslationImage->id,
            'alt_text' => null,
        ]);
    }

    public function test_alt_text_cannot_exceed_255_characters(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);

        $response = $this->actingAs($user)
            ->put(route('partner-translations.partner-translation-images.update', [$this->partnerTranslation, $this->partnerTranslationImage]), [
                'alt_text' => str_repeat('a', 256),
            ]);

        $response->assertSessionHasErrors('alt_text');
    }

    public function test_cannot_update_image_from_different_partner_translation(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);
        $otherPartnerTranslation = PartnerTranslation::factory()->create();
        $otherPartnerTranslationImage = PartnerTranslationImage::factory()->for($otherPartnerTranslation, 'partnerTranslation')->create();

        $response = $this->actingAs($user)
            ->put(route('partner-translations.partner-translation-images.update', [$this->partnerTranslation, $otherPartnerTranslationImage]), [
                'alt_text' => 'Should not work',
            ]);

        $response->assertNotFound();
    }

    public function test_guest_cannot_update_image(): void
    {
        $response = $this->put(route('partner-translations.partner-translation-images.update', [$this->partnerTranslation, $this->partnerTranslationImage]), [
            'alt_text' => 'Should not work',
        ]);

        $response->assertRedirect(route('login'));
    }
}

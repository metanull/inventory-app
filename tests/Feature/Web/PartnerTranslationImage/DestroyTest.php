<?php

namespace Tests\Feature\Web\PartnerTranslationImage;

use App\Models\AvailableImage;
use App\Models\PartnerTranslation;
use App\Models\PartnerTranslationImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DestroyTest extends TestCase
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

    public function test_authenticated_user_can_delete_image(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);

        $response = $this->actingAs($user)
            ->delete(route('partner-translations.partner-translation-images.destroy', [$this->partnerTranslation, $this->partnerTranslationImage]));

        $response->assertRedirect(route('partner-translations.show', $this->partnerTranslation));
        $response->assertSessionHas('success', 'Image deleted permanently');

        $this->assertDatabaseMissing('partner_translation_images', [
            'id' => $this->partnerTranslationImage->id,
        ]);
    }

    public function test_delete_is_permanent(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);
        $imagePath = $this->partnerTranslationImage->path;

        $this->actingAs($user)
            ->delete(route('partner-translations.partner-translation-images.destroy', [$this->partnerTranslation, $this->partnerTranslationImage]));

        // Should NOT create an AvailableImage (unlike detach)
        $this->assertDatabaseMissing('available_images', [
            'path' => $imagePath,
        ]);
    }

    public function test_cannot_delete_image_from_different_partner_translation(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);
        $otherPartnerTranslation = PartnerTranslation::factory()->create();
        $otherPartnerTranslationImage = PartnerTranslationImage::factory()->for($otherPartnerTranslation, 'partnerTranslation')->create();

        $response = $this->actingAs($user)
            ->delete(route('partner-translations.partner-translation-images.destroy', [$this->partnerTranslation, $otherPartnerTranslationImage]));

        $response->assertNotFound();
    }

    public function test_guest_cannot_delete_image(): void
    {
        $response = $this->delete(route('partner-translations.partner-translation-images.destroy', [$this->partnerTranslation, $this->partnerTranslationImage]));

        $response->assertRedirect(route('login'));
    }
}

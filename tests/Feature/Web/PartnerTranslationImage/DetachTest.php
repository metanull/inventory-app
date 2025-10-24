<?php

namespace Tests\Feature\Web\PartnerTranslationImage;

use App\Models\AvailableImage;
use App\Models\PartnerTranslation;
use App\Models\PartnerTranslationImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class DetachTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected PartnerTranslation $partnerTranslation;

    protected PartnerTranslationImage $partnerTranslationImage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->partnerTranslation = PartnerTranslation::factory()->create();
        $this->partnerTranslationImage = PartnerTranslationImage::factory()->for($this->partnerTranslation, 'partnerTranslation')->create();
    }

    public function test_authenticated_user_can_detach_image(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);
        $imagePath = $this->partnerTranslationImage->path;

        $response = $this->actingAs($user)
            ->post(route('partner-translations.partner-translation-images.detach', [$this->partnerTranslation, $this->partnerTranslationImage]));

        $response->assertRedirect(route('partner-translations.show', $this->partnerTranslation));
        $response->assertSessionHas('success', 'Image detached and returned to available images');

        // PartnerTranslationImage should be deleted
        $this->assertDatabaseMissing('partner_translation_images', [
            'id' => $this->partnerTranslationImage->id,
        ]);

        // Should create new AvailableImage
        $this->assertDatabaseHas('available_images', [
            'path' => $imagePath,
        ]);
    }

    public function test_detached_image_preserves_path(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);
        $imagePath = $this->partnerTranslationImage->path;

        $this->actingAs($user)
            ->post(route('partner-translations.partner-translation-images.detach', [$this->partnerTranslation, $this->partnerTranslationImage]));

        $availableImage = AvailableImage::where('path', $imagePath)->first();
        $this->assertNotNull($availableImage);
        $this->assertEquals($imagePath, $availableImage->path);
    }

    public function test_cannot_detach_image_from_different_partner_translation(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);
        $otherPartnerTranslation = PartnerTranslation::factory()->create();
        $otherPartnerTranslationImage = PartnerTranslationImage::factory()->for($otherPartnerTranslation, 'partnerTranslation')->create();

        $response = $this->actingAs($user)
            ->post(route('partner-translations.partner-translation-images.detach', [$this->partnerTranslation, $otherPartnerTranslationImage]));

        $response->assertNotFound();
    }

    public function test_guest_cannot_detach_image(): void
    {
        $response = $this->post(route('partner-translations.partner-translation-images.detach', [$this->partnerTranslation, $this->partnerTranslationImage]));

        $response->assertRedirect(route('login'));
    }
}

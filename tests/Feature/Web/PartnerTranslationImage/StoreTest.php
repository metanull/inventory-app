<?php

namespace Tests\Feature\Web\PartnerTranslationImage;

use App\Models\AvailableImage;
use App\Models\PartnerTranslation;
use App\Models\PartnerTranslationImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class StoreTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected PartnerTranslation $partnerTranslation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->partnerTranslation = PartnerTranslation::factory()->create();
    }

    public function test_authenticated_user_can_attach_image(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);
        $availableImage = AvailableImage::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('partner-translations.partner-translation-images.store', $this->partnerTranslation), [
                'available_image_id' => $availableImage->id,
            ]);

        $response->assertRedirect(route('partner-translations.show', $this->partnerTranslation));
        $response->assertSessionHas('success', 'Image attached successfully');

        $this->assertDatabaseHas('partner_translation_images', [
            'partner_translation_id' => $this->partnerTranslation->id,
            'path' => $availableImage->path,
        ]);

        // Available image should be deleted after attachment
        $this->assertDatabaseMissing('available_images', [
            'id' => $availableImage->id,
        ]);
    }

    public function test_attached_image_gets_display_order(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);
        $availableImage = AvailableImage::factory()->create();

        $this->actingAs($user)
            ->post(route('partner-translations.partner-translation-images.store', $this->partnerTranslation), [
                'available_image_id' => $availableImage->id,
            ]);

        $partnerTranslationImage = PartnerTranslationImage::where('partner_translation_id', $this->partnerTranslation->id)->first();
        $this->assertNotNull($partnerTranslationImage->display_order);
        $this->assertGreaterThan(0, $partnerTranslationImage->display_order);
    }

    public function test_validates_available_image_id_required(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);

        $response = $this->actingAs($user)
            ->post(route('partner-translations.partner-translation-images.store', $this->partnerTranslation), []);

        $response->assertSessionHasErrors(['available_image_id']);
    }

    public function test_store_validates_available_image_exists(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);

        $response = $this->actingAs($user)
            ->post(route('partner-translations.partner-translation-images.store', $this->partnerTranslation), [
                'available_image_id' => 'non-existent-uuid',
            ]);

        $response->assertSessionHasErrors('available_image_id');
    }

    public function test_store_requires_valid_uuid(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);

        $response = $this->actingAs($user)
            ->post(route('partner-translations.partner-translation-images.store', $this->partnerTranslation), [
                'available_image_id' => 'not-a-uuid',
            ]);

        $response->assertSessionHasErrors('available_image_id');
    }

    public function test_guest_cannot_attach_image(): void
    {
        $availableImage = AvailableImage::factory()->create();

        $response = $this->post(route('partner-translations.partner-translation-images.store', $this->partnerTranslation), [
            'available_image_id' => $availableImage->id,
        ]);

        $response->assertRedirect(route('login'));
    }
}

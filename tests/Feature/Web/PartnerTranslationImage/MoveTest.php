<?php

namespace Tests\Feature\Web\PartnerTranslationImage;

use App\Models\PartnerTranslation;
use App\Models\PartnerTranslationImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class MoveTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected PartnerTranslation $partnerTranslation;

    protected PartnerTranslationImage $image1;

    protected PartnerTranslationImage $image2;

    protected PartnerTranslationImage $image3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->partnerTranslation = PartnerTranslation::factory()->create();

        // Create 3 images with sequential display orders
        $this->image1 = PartnerTranslationImage::factory()->for($this->partnerTranslation, 'partnerTranslation')->create(['display_order' => 1]);
        $this->image2 = PartnerTranslationImage::factory()->for($this->partnerTranslation, 'partnerTranslation')->create(['display_order' => 2]);
        $this->image3 = PartnerTranslationImage::factory()->for($this->partnerTranslation, 'partnerTranslation')->create(['display_order' => 3]);
    }

    public function test_authenticated_user_can_move_image_up(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);
        $originalOrder = $this->image2->display_order;

        $response = $this->actingAs($user)
            ->post(route('partner-translations.partner-translation-images.move-up', [$this->partnerTranslation, $this->image2]));

        $response->assertRedirect(route('partner-translations.show', $this->partnerTranslation));
        $response->assertSessionHas('success', 'Image moved up');

        $this->image2->refresh();
        $this->assertLessThan($originalOrder, $this->image2->display_order);
    }

    public function test_authenticated_user_can_move_image_down(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);
        $originalOrder = $this->image2->display_order;

        $response = $this->actingAs($user)
            ->post(route('partner-translations.partner-translation-images.move-down', [$this->partnerTranslation, $this->image2]));

        $response->assertRedirect(route('partner-translations.show', $this->partnerTranslation));
        $response->assertSessionHas('success', 'Image moved down');

        $this->image2->refresh();
        $this->assertGreaterThan($originalOrder, $this->image2->display_order);
    }

    public function test_cannot_move_image_from_different_partner_translation(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);
        $otherPartnerTranslation = PartnerTranslation::factory()->create();
        $otherPartnerTranslationImage = PartnerTranslationImage::factory()->for($otherPartnerTranslation, 'partnerTranslation')->create();

        $response = $this->actingAs($user)
            ->post(route('partner-translations.partner-translation-images.move-up', [$this->partnerTranslation, $otherPartnerTranslationImage]));

        $response->assertNotFound();

        $response = $this->actingAs($user)
            ->post(route('partner-translations.partner-translation-images.move-down', [$this->partnerTranslation, $otherPartnerTranslationImage]));

        $response->assertNotFound();
    }

    public function test_guest_cannot_move_images(): void
    {
        $response = $this->post(route('partner-translations.partner-translation-images.move-up', [$this->partnerTranslation, $this->image2]));
        $response->assertRedirect(route('login'));

        $response = $this->post(route('partner-translations.partner-translation-images.move-down', [$this->partnerTranslation, $this->image2]));
        $response->assertRedirect(route('login'));
    }
}

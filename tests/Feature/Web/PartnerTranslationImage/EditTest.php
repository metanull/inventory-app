<?php

namespace Tests\Feature\Web\PartnerTranslationImage;

use App\Models\PartnerTranslation;
use App\Models\PartnerTranslationImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class EditTest extends TestCase
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

    public function test_authenticated_user_can_view_edit_form(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);

        $response = $this->actingAs($user)
            ->get(route('partner-translations.partner-translation-images.edit', [$this->partnerTranslation, $this->partnerTranslationImage]));

        $response->assertOk();
        $response->assertViewIs('partner-translation-images.edit');
        $response->assertViewHas('partnerTranslation', $this->partnerTranslation);
        $response->assertViewHas('partnerTranslationImage', $this->partnerTranslationImage);
        $response->assertSee('Edit Image Alt Text');
    }

    public function test_edit_form_displays_current_alt_text(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);
        $this->partnerTranslationImage->update(['alt_text' => 'Test Alt Text']);

        $response = $this->actingAs($user)
            ->get(route('partner-translations.partner-translation-images.edit', [$this->partnerTranslation, $this->partnerTranslationImage]));

        $response->assertOk();
        $response->assertSee('Test Alt Text');
    }

    public function test_edit_form_displays_display_order(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);

        $response = $this->actingAs($user)
            ->get(route('partner-translations.partner-translation-images.edit', [$this->partnerTranslation, $this->partnerTranslationImage]));

        $response->assertOk();
        $response->assertSee('Display Order');
        $response->assertSee((string) $this->partnerTranslationImage->display_order);
    }

    public function test_cannot_edit_image_from_different_partner_translation(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);
        $otherPartnerTranslation = PartnerTranslation::factory()->create();
        $otherPartnerTranslationImage = PartnerTranslationImage::factory()->for($otherPartnerTranslation, 'partnerTranslation')->create();

        $response = $this->actingAs($user)
            ->get(route('partner-translations.partner-translation-images.edit', [$this->partnerTranslation, $otherPartnerTranslationImage]));

        $response->assertNotFound();
    }

    public function test_guest_cannot_view_edit_form(): void
    {
        $response = $this->get(route('partner-translations.partner-translation-images.edit', [$this->partnerTranslation, $this->partnerTranslationImage]));

        $response->assertRedirect(route('login'));
    }
}

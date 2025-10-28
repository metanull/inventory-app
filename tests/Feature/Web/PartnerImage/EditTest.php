<?php

namespace Tests\Feature\Web\PartnerImage;

use App\Models\Partner;
use App\Models\PartnerImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class EditTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected Partner $partner;

    protected PartnerImage $partnerImage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->partner = Partner::factory()->create();
        $this->partnerImage = PartnerImage::factory()->for($this->partner)->create();
    }

    public function test_authenticated_user_can_view_edit_form(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);

        $response = $this->actingAs($user)
            ->get(route('partners.partner-images.edit', [$this->partner, $this->partnerImage]));

        $response->assertOk();
        $response->assertViewIs('partner-images.edit');
        $response->assertViewHas('partner', $this->partner);
        $response->assertViewHas('partnerImage', $this->partnerImage);
        $response->assertSee('Edit Image Alt Text');
    }

    public function test_edit_form_displays_current_alt_text(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);
        $this->partnerImage->update(['alt_text' => 'Test Alt Text']);

        $response = $this->actingAs($user)
            ->get(route('partners.partner-images.edit', [$this->partner, $this->partnerImage]));

        $response->assertOk();
        $response->assertSee('Test Alt Text');
    }

    public function test_edit_form_displays_display_order(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);

        $response = $this->actingAs($user)
            ->get(route('partners.partner-images.edit', [$this->partner, $this->partnerImage]));

        $response->assertOk();
        $response->assertSee('Display Order');
        $response->assertSee((string) $this->partnerImage->display_order);
    }

    public function test_cannot_edit_image_from_different_partner(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);
        $otherPartner = Partner::factory()->create();
        $otherPartnerImage = PartnerImage::factory()->for($otherPartner)->create();

        $response = $this->actingAs($user)
            ->get(route('partners.partner-images.edit', [$this->partner, $otherPartnerImage]));

        $response->assertNotFound();
    }

    public function test_guest_cannot_view_edit_form(): void
    {
        $response = $this->get(route('partners.partner-images.edit', [$this->partner, $this->partnerImage]));

        $response->assertRedirect(route('login'));
    }
}

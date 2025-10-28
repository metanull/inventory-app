<?php

namespace Tests\Feature\Web\PartnerImage;

use App\Models\AvailableImage;
use App\Models\Partner;
use App\Models\PartnerImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DestroyTest extends TestCase
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

    public function test_authenticated_user_can_delete_image(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);

        $response = $this->actingAs($user)
            ->delete(route('partners.partner-images.destroy', [$this->partner, $this->partnerImage]));

        $response->assertRedirect(route('partners.show', $this->partner));
        $response->assertSessionHas('success', 'Image deleted permanently');

        $this->assertDatabaseMissing('partner_images', [
            'id' => $this->partnerImage->id,
        ]);
    }

    public function test_delete_is_permanent(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);
        $imagePath = $this->partnerImage->path;

        $this->actingAs($user)
            ->delete(route('partners.partner-images.destroy', [$this->partner, $this->partnerImage]));

        // Should NOT create an AvailableImage (unlike detach)
        $this->assertDatabaseMissing('available_images', [
            'path' => $imagePath,
        ]);
    }

    public function test_cannot_delete_image_from_different_partner(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);
        $otherPartner = Partner::factory()->create();
        $otherPartnerImage = PartnerImage::factory()->for($otherPartner)->create();

        $response = $this->actingAs($user)
            ->delete(route('partners.partner-images.destroy', [$this->partner, $otherPartnerImage]));

        $response->assertNotFound();
    }

    public function test_guest_cannot_delete_image(): void
    {
        $response = $this->delete(route('partners.partner-images.destroy', [$this->partner, $this->partnerImage]));

        $response->assertRedirect(route('login'));
    }
}

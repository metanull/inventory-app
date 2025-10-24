<?php

namespace Tests\Feature\Web\PartnerImage;

use App\Models\AvailableImage;
use App\Models\Partner;
use App\Models\PartnerImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class DetachTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected Partner $partner;

    protected PartnerImage $partnerImage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->partner = Partner::factory()->create();
        $this->partnerImage = PartnerImage::factory()->for($this->partner)->create();
    }

    public function test_authenticated_user_can_detach_image(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);
        $imagePath = $this->partnerImage->path;

        $response = $this->actingAs($user)
            ->post(route('partners.partner-images.detach', [$this->partner, $this->partnerImage]));

        $response->assertRedirect(route('partners.show', $this->partner));
        $response->assertSessionHas('success', 'Image detached and returned to available images');

        // PartnerImage should be deleted
        $this->assertDatabaseMissing('partner_images', [
            'id' => $this->partnerImage->id,
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
        $imagePath = $this->partnerImage->path;

        $this->actingAs($user)
            ->post(route('partners.partner-images.detach', [$this->partner, $this->partnerImage]));

        $availableImage = AvailableImage::where('path', $imagePath)->first();
        $this->assertNotNull($availableImage);
        $this->assertEquals($imagePath, $availableImage->path);
    }

    public function test_cannot_detach_image_from_different_partner(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);
        $otherPartner = Partner::factory()->create();
        $otherPartnerImage = PartnerImage::factory()->for($otherPartner)->create();

        $response = $this->actingAs($user)
            ->post(route('partners.partner-images.detach', [$this->partner, $otherPartnerImage]));

        $response->assertNotFound();
    }

    public function test_guest_cannot_detach_image(): void
    {
        $response = $this->post(route('partners.partner-images.detach', [$this->partner, $this->partnerImage]));

        $response->assertRedirect(route('login'));
    }
}

<?php

namespace Tests\Feature\Web\PartnerImage;

use App\Models\AvailableImage;
use App\Models\Partner;
use App\Models\PartnerImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class StoreTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected Partner $partner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->partner = Partner::factory()->create();
    }

    public function test_authenticated_user_can_attach_image(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);
        $availableImage = AvailableImage::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('partners.partner-images.store', $this->partner), [
                'available_image_id' => $availableImage->id,
            ]);

        $response->assertRedirect(route('partners.show', $this->partner));
        $response->assertSessionHas('success', 'Image attached successfully');

        $this->assertDatabaseHas('partner_images', [
            'partner_id' => $this->partner->id,
            'path' => $availableImage->path,
        ]);

        // Available image should be deleted after attachment
        $this->assertDatabaseMissing('available_images', [
            'id' => $availableImage->id,
        ]);
    }

    public function test_attached_image_gets_display_order(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);
        $availableImage = AvailableImage::factory()->create();

        $this->actingAs($user)
            ->post(route('partners.partner-images.store', $this->partner), [
                'available_image_id' => $availableImage->id,
            ]);

        $partnerImage = PartnerImage::where('partner_id', $this->partner->id)->first();
        $this->assertNotNull($partnerImage->display_order);
        $this->assertGreaterThan(0, $partnerImage->display_order);
    }

    public function test_validates_available_image_id_required(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);

        $response = $this->actingAs($user)
            ->post(route('partners.partner-images.store', $this->partner), []);

        $response->assertSessionHasErrors(['available_image_id']);
    }

    public function test_store_validates_available_image_exists(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);

        $response = $this->actingAs($user)
            ->post(route('partners.partner-images.store', $this->partner), [
                'available_image_id' => 'non-existent-uuid',
            ]);

        $response->assertSessionHasErrors('available_image_id');
    }

    public function test_store_requires_valid_uuid(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);

        $response = $this->actingAs($user)
            ->post(route('partners.partner-images.store', $this->partner), [
                'available_image_id' => 'not-a-uuid',
            ]);

        $response->assertSessionHasErrors('available_image_id');
    }

    public function test_guest_cannot_attach_image(): void
    {
        $availableImage = AvailableImage::factory()->create();

        $response = $this->post(route('partners.partner-images.store', $this->partner), [
            'available_image_id' => $availableImage->id,
        ]);

        $response->assertRedirect(route('login'));
    }
}

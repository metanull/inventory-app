<?php

namespace Tests\Feature\Web\PartnerImage;

use App\Models\Partner;
use App\Models\PartnerImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class MoveTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected Partner $partner;

    protected PartnerImage $image1;

    protected PartnerImage $image2;

    protected PartnerImage $image3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->partner = Partner::factory()->create();

        // Create 3 images with sequential display orders
        $this->image1 = PartnerImage::factory()->for($this->partner)->create(['display_order' => 1]);
        $this->image2 = PartnerImage::factory()->for($this->partner)->create(['display_order' => 2]);
        $this->image3 = PartnerImage::factory()->for($this->partner)->create(['display_order' => 3]);
    }

    public function test_authenticated_user_can_move_image_up(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);
        $originalOrder = $this->image2->display_order;

        $response = $this->actingAs($user)
            ->post(route('partners.partner-images.move-up', [$this->partner, $this->image2]));

        $response->assertRedirect(route('partners.show', $this->partner));
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
            ->post(route('partners.partner-images.move-down', [$this->partner, $this->image2]));

        $response->assertRedirect(route('partners.show', $this->partner));
        $response->assertSessionHas('success', 'Image moved down');

        $this->image2->refresh();
        $this->assertGreaterThan($originalOrder, $this->image2->display_order);
    }

    public function test_cannot_move_image_from_different_partner(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);
        $otherPartner = Partner::factory()->create();
        $otherPartnerImage = PartnerImage::factory()->for($otherPartner)->create();

        $response = $this->actingAs($user)
            ->post(route('partners.partner-images.move-up', [$this->partner, $otherPartnerImage]));

        $response->assertNotFound();

        $response = $this->actingAs($user)
            ->post(route('partners.partner-images.move-down', [$this->partner, $otherPartnerImage]));

        $response->assertNotFound();
    }

    public function test_guest_cannot_move_images(): void
    {
        $response = $this->post(route('partners.partner-images.move-up', [$this->partner, $this->image2]));
        $response->assertRedirect(route('login'));

        $response = $this->post(route('partners.partner-images.move-down', [$this->partner, $this->image2]));
        $response->assertRedirect(route('login'));
    }
}

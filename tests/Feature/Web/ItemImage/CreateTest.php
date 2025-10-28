<?php

namespace Tests\Feature\Web\ItemImage;

use App\Models\AvailableImage;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class CreateTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected Item $item;

    protected function setUp(): void
    {
        parent::setUp();

        $this->item = Item::factory()->create();
    }

    public function test_authenticated_user_can_view_attach_image_form(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);

        $response = $this->actingAs($user)
            ->get(route('items.item-images.create', $this->item));

        $response->assertOk();
        $response->assertViewIs('item-images.create');
        $response->assertViewHas('item', $this->item);
        $response->assertViewHas('availableImages');
        $response->assertSee('Attach Image to');
        $response->assertSee($this->item->internal_name);
    }

    public function test_create_form_displays_available_images(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);
        $availableImage1 = AvailableImage::factory()->create(['comment' => 'Test Image 1']);
        $availableImage2 = AvailableImage::factory()->create(['comment' => 'Test Image 2']);

        $response = $this->actingAs($user)
            ->get(route('items.item-images.create', $this->item));

        $response->assertOk();
        $response->assertSee('Test Image 1');
        $response->assertSee('Test Image 2');
        $response->assertSee($availableImage1->id);
        $response->assertSee($availableImage2->id);
    }

    public function test_create_form_shows_empty_state_when_no_available_images(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);

        $response = $this->actingAs($user)
            ->get(route('items.item-images.create', $this->item));

        $response->assertOk();
        $response->assertSee('No available images');
        $response->assertSee('Upload images to the available images pool first');
    }

    public function test_guest_cannot_view_attach_image_form(): void
    {
        $response = $this->get(route('items.item-images.create', $this->item));

        $response->assertRedirect(route('login'));
    }
}

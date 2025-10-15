<?php

namespace Tests\Feature\Web\ItemImage;

use App\Models\Item;
use App\Models\ItemImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class MoveTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected Item $item;

    protected ItemImage $image1;

    protected ItemImage $image2;

    protected ItemImage $image3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->item = Item::factory()->create();

        // Create 3 images with sequential display orders
        $this->image1 = ItemImage::factory()->for($this->item)->create(['display_order' => 1]);
        $this->image2 = ItemImage::factory()->for($this->item)->create(['display_order' => 2]);
        $this->image3 = ItemImage::factory()->for($this->item)->create(['display_order' => 3]);
    }

    public function test_authenticated_user_can_move_image_up(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);
        $originalOrder = $this->image2->display_order;

        $response = $this->actingAs($user)
            ->post(route('items.item-images.move-up', [$this->item, $this->image2]));

        $response->assertRedirect(route('items.show', $this->item));
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
            ->post(route('items.item-images.move-down', [$this->item, $this->image2]));

        $response->assertRedirect(route('items.show', $this->item));
        $response->assertSessionHas('success', 'Image moved down');

        $this->image2->refresh();
        $this->assertGreaterThan($originalOrder, $this->image2->display_order);
    }

    public function test_cannot_move_image_from_different_item(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);
        $otherItem = Item::factory()->create();
        $otherItemImage = ItemImage::factory()->for($otherItem)->create();

        $response = $this->actingAs($user)
            ->post(route('items.item-images.move-up', [$this->item, $otherItemImage]));

        $response->assertNotFound();

        $response = $this->actingAs($user)
            ->post(route('items.item-images.move-down', [$this->item, $otherItemImage]));

        $response->assertNotFound();
    }

    public function test_guest_cannot_move_images(): void
    {
        $response = $this->post(route('items.item-images.move-up', [$this->item, $this->image2]));
        $response->assertRedirect(route('login'));

        $response = $this->post(route('items.item-images.move-down', [$this->item, $this->image2]));
        $response->assertRedirect(route('login'));
    }
}

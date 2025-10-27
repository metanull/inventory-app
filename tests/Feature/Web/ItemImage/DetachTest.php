<?php

namespace Tests\Feature\Web\ItemImage;

use App\Models\AvailableImage;
use App\Models\Item;
use App\Models\ItemImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class DetachTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected Item $item;

    protected ItemImage $itemImage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->item = Item::factory()->create();
        $this->itemImage = ItemImage::factory()->for($this->item)->create();
    }

    public function test_authenticated_user_can_detach_image(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);
        $imagePath = $this->itemImage->path;

        $response = $this->actingAs($user)
            ->delete(route('items.item-images.detach', [$this->item, $this->itemImage]));

        $response->assertRedirect(route('items.show', $this->item));
        $response->assertSessionHas('success', 'Image detached and returned to available images');

        // ItemImage should be deleted
        $this->assertDatabaseMissing('item_images', [
            'id' => $this->itemImage->id,
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
        $imagePath = $this->itemImage->path;

        $this->actingAs($user)
            ->delete(route('items.item-images.detach', [$this->item, $this->itemImage]));

        $availableImage = AvailableImage::where('path', $imagePath)->first();
        $this->assertNotNull($availableImage);
        $this->assertEquals($imagePath, $availableImage->path);
    }

    public function test_cannot_detach_image_from_different_item(): void
    {
        $this->actAsRegularUser();
        $user = User::find(1);
        $otherItem = Item::factory()->create();
        $otherItemImage = ItemImage::factory()->for($otherItem)->create();

        $response = $this->actingAs($user)
            ->delete(route('items.item-images.detach', [$this->item, $otherItemImage]));

        $response->assertNotFound();
    }

    public function test_guest_cannot_detach_image(): void
    {
        $response = $this->delete(route('items.item-images.detach', [$this->item, $this->itemImage]));

        $response->assertRedirect(route('login'));
    }
}

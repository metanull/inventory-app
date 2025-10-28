<?php

namespace Tests\Feature\Web\ItemImage;

use App\Models\AvailableImage;
use App\Models\Item;
use App\Models\ItemImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DestroyTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected Item $item;

    protected ItemImage $itemImage;

    protected function setUp(): void
    {
        parent::setUp();

        $this->item = Item::factory()->create();
        $this->itemImage = ItemImage::factory()->for($this->item)->create();
    }

    public function test_authenticated_user_can_delete_image(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);

        $response = $this->actingAs($user)
            ->delete(route('items.item-images.destroy', [$this->item, $this->itemImage]));

        $response->assertRedirect(route('items.show', $this->item));
        $response->assertSessionHas('success', 'Image deleted permanently');

        $this->assertDatabaseMissing('item_images', [
            'id' => $this->itemImage->id,
        ]);
    }

    public function test_delete_is_permanent(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);
        $imagePath = $this->itemImage->path;

        $this->actingAs($user)
            ->delete(route('items.item-images.destroy', [$this->item, $this->itemImage]));

        // Should NOT create an AvailableImage (unlike detach)
        $this->assertDatabaseMissing('available_images', [
            'path' => $imagePath,
        ]);
    }

    public function test_cannot_delete_image_from_different_item(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);
        $otherItem = Item::factory()->create();
        $otherItemImage = ItemImage::factory()->for($otherItem)->create();

        $response = $this->actingAs($user)
            ->delete(route('items.item-images.destroy', [$this->item, $otherItemImage]));

        $response->assertNotFound();
    }

    public function test_guest_cannot_delete_image(): void
    {
        $response = $this->delete(route('items.item-images.destroy', [$this->item, $this->itemImage]));

        $response->assertRedirect(route('login'));
    }
}

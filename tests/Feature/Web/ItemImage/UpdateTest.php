<?php

namespace Tests\Feature\Web\ItemImage;

use App\Models\Item;
use App\Models\ItemImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class UpdateTest extends TestCase
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

    public function test_authenticated_user_can_update_alt_text(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);

        $response = $this->actingAs($user)
            ->put(route('items.item-images.update', [$this->item, $this->itemImage]), [
                'alt_text' => 'Updated Alt Text',
            ]);

        $response->assertRedirect(route('items.show', $this->item));
        $response->assertSessionHas('success', 'Image updated successfully');

        $this->assertDatabaseHas('item_images', [
            'id' => $this->itemImage->id,
            'alt_text' => 'Updated Alt Text',
        ]);
    }

    public function test_alt_text_can_be_null(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);
        $this->itemImage->update(['alt_text' => 'Some text']);

        $response = $this->actingAs($user)
            ->put(route('items.item-images.update', [$this->item, $this->itemImage]), [
                'alt_text' => null,
            ]);

        $response->assertRedirect(route('items.show', $this->item));

        $this->assertDatabaseHas('item_images', [
            'id' => $this->itemImage->id,
            'alt_text' => null,
        ]);
    }

    public function test_alt_text_cannot_exceed_255_characters(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);

        $response = $this->actingAs($user)
            ->put(route('items.item-images.update', [$this->item, $this->itemImage]), [
                'alt_text' => str_repeat('a', 256),
            ]);

        $response->assertSessionHasErrors('alt_text');
    }

    public function test_cannot_update_image_from_different_item(): void
    {
        $this->actingAsDataUser();
        $user = User::find(1);
        $otherItem = Item::factory()->create();
        $otherItemImage = ItemImage::factory()->for($otherItem)->create();

        $response = $this->actingAs($user)
            ->put(route('items.item-images.update', [$this->item, $otherItemImage]), [
                'alt_text' => 'Should not work',
            ]);

        $response->assertNotFound();
    }

    public function test_guest_cannot_update_image(): void
    {
        $response = $this->put(route('items.item-images.update', [$this->item, $this->itemImage]), [
            'alt_text' => 'Should not work',
        ]);

        $response->assertRedirect(route('login'));
    }
}

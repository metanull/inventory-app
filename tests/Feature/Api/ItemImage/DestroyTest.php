<?php

namespace Tests\Feature\Api\ItemImage;

use App\Models\Item;
use App\Models\ItemImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_destroy_deletes_item_image_successfully(): void
    {
        $itemImage = ItemImage::factory()->create();

        $response = $this->deleteJson(route('item-image.destroy', $itemImage->id));

        $response->assertNoContent();
        $this->assertDatabaseMissing('item_images', [
            'id' => $itemImage->id,
        ]);
    }

    public function test_destroy_returns_not_found_for_nonexistent_item_image(): void
    {
        $response = $this->deleteJson(route('item-image.destroy', 'nonexistent-uuid'));

        $response->assertNotFound();
    }

    public function test_destroy_adjusts_display_order_of_remaining_images(): void
    {
        $item = Item::factory()->create();
        $image1 = ItemImage::factory()->forItem($item)->withOrder(1)->create();
        $image2 = ItemImage::factory()->forItem($item)->withOrder(2)->create();
        $image3 = ItemImage::factory()->forItem($item)->withOrder(3)->create();
        $image4 = ItemImage::factory()->forItem($item)->withOrder(4)->create();

        // Delete the second image
        $response = $this->deleteJson(route('item-image.destroy', $image2->id));

        $response->assertNoContent();

        // Check that remaining images have been reordered
        $image1->refresh();
        $image3->refresh();
        $image4->refresh();

        $this->assertEquals(1, $image1->display_order);
        $this->assertEquals(2, $image3->display_order); // Was 3, now 2
        $this->assertEquals(3, $image4->display_order); // Was 4, now 3
    }

    public function test_destroy_handles_single_image(): void
    {
        $itemImage = ItemImage::factory()->create();

        $response = $this->deleteJson(route('item-image.destroy', $itemImage->id));

        $response->assertNoContent();
        $this->assertDatabaseMissing('item_images', [
            'id' => $itemImage->id,
        ]);
    }

    public function test_destroy_only_affects_images_from_same_item(): void
    {
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();

        $item1Image1 = ItemImage::factory()->forItem($item1)->withOrder(1)->create();
        $item1Image2 = ItemImage::factory()->forItem($item1)->withOrder(2)->create();
        $item2Image1 = ItemImage::factory()->forItem($item2)->withOrder(1)->create();
        $item2Image2 = ItemImage::factory()->forItem($item2)->withOrder(2)->create();

        // Delete image from item1
        $response = $this->deleteJson(route('item-image.destroy', $item1Image1->id));

        $response->assertNoContent();

        // Check that only item1's images are affected
        $item1Image2->refresh();
        $item2Image1->refresh();
        $item2Image2->refresh();

        $this->assertEquals(1, $item1Image2->display_order); // Reordered from 2 to 1
        $this->assertEquals(1, $item2Image1->display_order); // Unchanged
        $this->assertEquals(2, $item2Image2->display_order); // Unchanged
    }

    public function test_destroy_handles_gaps_in_display_order(): void
    {
        $item = Item::factory()->create();
        $image1 = ItemImage::factory()->forItem($item)->withOrder(1)->create();
        $image5 = ItemImage::factory()->forItem($item)->withOrder(5)->create();
        $image10 = ItemImage::factory()->forItem($item)->withOrder(10)->create();

        // Delete the middle image
        $response = $this->deleteJson(route('item-image.destroy', $image5->id));

        $response->assertNoContent();

        // Check that remaining images are properly reordered
        $image1->refresh();
        $image10->refresh();

        $this->assertEquals(1, $image1->display_order);
        $this->assertEquals(2, $image10->display_order); // Was 10, now 2
    }

    public function test_destroy_multiple_images_in_sequence(): void
    {
        $item = Item::factory()->create();
        $images = collect();

        for ($i = 1; $i <= 5; $i++) {
            $images->push(ItemImage::factory()->forItem($item)->withOrder($i)->create());
        }

        // Delete images 2 and 4
        $this->deleteJson(route('item-image.destroy', $images[1]->id))->assertNoContent();
        $this->deleteJson(route('item-image.destroy', $images[3]->id))->assertNoContent();

        // Check that remaining images are properly reordered
        $images[0]->refresh(); // Was order 1
        $images[2]->refresh(); // Was order 3
        $images[4]->refresh(); // Was order 5

        $this->assertEquals(1, $images[0]->display_order);
        $this->assertEquals(2, $images[2]->display_order);
        $this->assertEquals(3, $images[4]->display_order);

        // Verify only 3 images remain
        $this->assertEquals(3, $item->itemImages()->count());
    }

    public function test_destroy_last_image_of_item(): void
    {
        $item = Item::factory()->create();
        $itemImage = ItemImage::factory()->forItem($item)->withOrder(1)->create();

        $response = $this->deleteJson(route('item-image.destroy', $itemImage->id));

        $response->assertNoContent();
        $this->assertEquals(0, $item->itemImages()->count());
        $this->assertDatabaseMissing('item_images', [
            'id' => $itemImage->id,
        ]);
    }

    public function test_destroy_preserves_other_item_relationships(): void
    {
        $item = Item::factory()->create();
        $itemImage = ItemImage::factory()->forItem($item)->create();

        // Ensure the item still exists after deleting its image
        $response = $this->deleteJson(route('item-image.destroy', $itemImage->id));

        $response->assertNoContent();
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
        ]);
    }
}

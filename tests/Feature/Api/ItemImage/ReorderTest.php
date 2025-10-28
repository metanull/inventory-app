<?php

namespace Tests\Feature\Api\ItemImage;

use App\Enums\Permission;
use App\Models\Item;
use App\Models\ItemImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class ReorderTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWith(Permission::dataOperations());
        $this->actingAs($this->user);
    }

    public function test_move_up_moves_image_up_in_order(): void
    {
        $item = Item::factory()->create();
        $image1 = ItemImage::factory()->forItem($item)->withOrder(1)->create();
        $image2 = ItemImage::factory()->forItem($item)->withOrder(2)->create();
        $image3 = ItemImage::factory()->forItem($item)->withOrder(3)->create();

        $response = $this->patchJson(route('item-image.moveUp', $image3->id));

        $response->assertOk();

        // Refresh models
        $image1->refresh();
        $image2->refresh();
        $image3->refresh();

        // image3 should move up, image2 should move down
        $this->assertEquals(1, $image1->display_order); // Unchanged
        $this->assertEquals(3, $image2->display_order); // Moved down
        $this->assertEquals(2, $image3->display_order); // Moved up
    }

    public function test_move_up_at_top_position_has_no_effect(): void
    {
        $item = Item::factory()->create();
        $image1 = ItemImage::factory()->forItem($item)->withOrder(1)->create();
        $image2 = ItemImage::factory()->forItem($item)->withOrder(2)->create();

        $response = $this->patchJson(route('item-image.moveUp', $image1->id));

        $response->assertOk();

        // Orders should remain unchanged
        $image1->refresh();
        $image2->refresh();

        $this->assertEquals(1, $image1->display_order);
        $this->assertEquals(2, $image2->display_order);
    }

    public function test_move_down_moves_image_down_in_order(): void
    {
        $item = Item::factory()->create();
        $image1 = ItemImage::factory()->forItem($item)->withOrder(1)->create();
        $image2 = ItemImage::factory()->forItem($item)->withOrder(2)->create();
        $image3 = ItemImage::factory()->forItem($item)->withOrder(3)->create();

        $response = $this->patchJson(route('item-image.moveDown', $image1->id));

        $response->assertOk();

        // Refresh models
        $image1->refresh();
        $image2->refresh();
        $image3->refresh();

        // image1 should move down, image2 should move up
        $this->assertEquals(2, $image1->display_order); // Moved down
        $this->assertEquals(1, $image2->display_order); // Moved up
        $this->assertEquals(3, $image3->display_order); // Unchanged
    }

    public function test_move_down_at_bottom_position_has_no_effect(): void
    {
        $item = Item::factory()->create();
        $image1 = ItemImage::factory()->forItem($item)->withOrder(1)->create();
        $image2 = ItemImage::factory()->forItem($item)->withOrder(2)->create();
        $image3 = ItemImage::factory()->forItem($item)->withOrder(3)->create();

        $response = $this->patchJson(route('item-image.moveDown', $image3->id));

        $response->assertOk();

        // Orders should remain unchanged
        $image1->refresh();
        $image2->refresh();
        $image3->refresh();

        $this->assertEquals(1, $image1->display_order);
        $this->assertEquals(2, $image2->display_order);
        $this->assertEquals(3, $image3->display_order);
    }

    public function test_tighten_ordering_removes_gaps(): void
    {
        $item = Item::factory()->create();
        $image1 = ItemImage::factory()->forItem($item)->withOrder(1)->create();
        $image5 = ItemImage::factory()->forItem($item)->withOrder(5)->create();
        $image10 = ItemImage::factory()->forItem($item)->withOrder(10)->create();
        $image15 = ItemImage::factory()->forItem($item)->withOrder(15)->create();

        $response = $this->patchJson(route('item-image.tightenOrdering', $image1->id));

        $response->assertOk();

        // Refresh models
        $image1->refresh();
        $image5->refresh();
        $image10->refresh();
        $image15->refresh();

        // Orders should be tightened to 1, 2, 3, 4
        $this->assertEquals(1, $image1->display_order);
        $this->assertEquals(2, $image5->display_order);
        $this->assertEquals(3, $image10->display_order);
        $this->assertEquals(4, $image15->display_order);
    }

    public function test_tighten_ordering_only_affects_same_item(): void
    {
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();

        $item1Image1 = ItemImage::factory()->forItem($item1)->withOrder(1)->create();
        $item1Image5 = ItemImage::factory()->forItem($item1)->withOrder(5)->create();
        $item2Image1 = ItemImage::factory()->forItem($item2)->withOrder(1)->create();
        $item2Image10 = ItemImage::factory()->forItem($item2)->withOrder(10)->create();

        $response = $this->patchJson(route('item-image.tightenOrdering', $item1Image1->id));

        $response->assertOk();

        // Refresh models
        $item1Image1->refresh();
        $item1Image5->refresh();
        $item2Image1->refresh();
        $item2Image10->refresh();

        // Only item1's images should be tightened
        $this->assertEquals(1, $item1Image1->display_order);
        $this->assertEquals(2, $item1Image5->display_order);
        $this->assertEquals(1, $item2Image1->display_order); // Unchanged
        $this->assertEquals(10, $item2Image10->display_order); // Unchanged
    }

    public function test_move_operations_only_affect_same_item(): void
    {
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();

        $item1Image1 = ItemImage::factory()->forItem($item1)->withOrder(1)->create();
        $item1Image2 = ItemImage::factory()->forItem($item1)->withOrder(2)->create();
        $item2Image1 = ItemImage::factory()->forItem($item2)->withOrder(1)->create();
        $item2Image2 = ItemImage::factory()->forItem($item2)->withOrder(2)->create();

        $response = $this->patchJson(route('item-image.moveDown', $item1Image1->id));

        $response->assertOk();

        // Refresh models
        $item1Image1->refresh();
        $item1Image2->refresh();
        $item2Image1->refresh();
        $item2Image2->refresh();

        // Only item1's images should be affected
        $this->assertEquals(2, $item1Image1->display_order);
        $this->assertEquals(1, $item1Image2->display_order);
        $this->assertEquals(1, $item2Image1->display_order); // Unchanged
        $this->assertEquals(2, $item2Image2->display_order); // Unchanged
    }

    public function test_move_up_returns_correct_structure(): void
    {
        $item = Item::factory()->create();
        $image1 = ItemImage::factory()->forItem($item)->withOrder(1)->create();
        $image2 = ItemImage::factory()->forItem($item)->withOrder(2)->create();

        $response = $this->patchJson(route('item-image.moveUp', $image2->id));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'item_id',
                'path',
                'original_name',
                'mime_type',
                'size',
                'alt_text',
                'display_order',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_move_operations_return_not_found_for_nonexistent_image(): void
    {
        $response = $this->patchJson(route('item-image.moveUp', 'nonexistent-uuid'));
        $response->assertNotFound();

        $response = $this->patchJson(route('item-image.moveDown', 'nonexistent-uuid'));
        $response->assertNotFound();

        $response = $this->patchJson(route('item-image.tightenOrdering', 'nonexistent-uuid'));
        $response->assertNotFound();
    }

    public function test_single_image_move_operations_have_no_effect(): void
    {
        $itemImage = ItemImage::factory()->withOrder(1)->create();

        $response = $this->patchJson(route('item-image.moveUp', $itemImage->id));
        $response->assertOk();

        $itemImage->refresh();
        $this->assertEquals(1, $itemImage->display_order);

        $response = $this->patchJson(route('item-image.moveDown', $itemImage->id));
        $response->assertOk();

        $itemImage->refresh();
        $this->assertEquals(1, $itemImage->display_order);
    }

    public function test_tighten_ordering_with_single_image(): void
    {
        $itemImage = ItemImage::factory()->withOrder(5)->create();

        $response = $this->patchJson(route('item-image.tightenOrdering', $itemImage->id));

        $response->assertOk();

        $itemImage->refresh();
        $this->assertEquals(1, $itemImage->display_order);
    }

    public function test_move_operations_handle_duplicate_orders(): void
    {
        $item = Item::factory()->create();
        // Create images with duplicate orders (edge case)
        $image1 = ItemImage::factory()->forItem($item)->create(['display_order' => 2]);
        $image2 = ItemImage::factory()->forItem($item)->create(['display_order' => 2]);
        $image3 = ItemImage::factory()->forItem($item)->create(['display_order' => 3]);

        $response = $this->patchJson(route('item-image.moveUp', $image3->id));

        $response->assertOk();

        // Should handle the duplicate orders gracefully
        $image3->refresh();
        $this->assertLessThan(3, $image3->display_order);
    }
}

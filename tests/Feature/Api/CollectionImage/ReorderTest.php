<?php

namespace Tests\Feature\Api\CollectionImage;

use App\Models\Collection;
use App\Models\CollectionImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class ReorderTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function test_move_up_moves_image_up_in_order(): void
    {
        $collection = Collection::factory()->create();
        $image1 = CollectionImage::factory()->forCollection($collection)->withOrder(1)->create();
        $image2 = CollectionImage::factory()->forCollection($collection)->withOrder(2)->create();
        $image3 = CollectionImage::factory()->forCollection($collection)->withOrder(3)->create();

        $response = $this->patchJson(route('collection-image.moveUp', $image3->id));

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
        $collection = Collection::factory()->create();
        $image1 = CollectionImage::factory()->forCollection($collection)->withOrder(1)->create();
        $image2 = CollectionImage::factory()->forCollection($collection)->withOrder(2)->create();

        $response = $this->patchJson(route('collection-image.moveUp', $image1->id));

        $response->assertOk();

        // Orders should remain unchanged
        $image1->refresh();
        $image2->refresh();

        $this->assertEquals(1, $image1->display_order);
        $this->assertEquals(2, $image2->display_order);
    }

    public function test_move_down_moves_image_down_in_order(): void
    {
        $collection = Collection::factory()->create();
        $image1 = CollectionImage::factory()->forCollection($collection)->withOrder(1)->create();
        $image2 = CollectionImage::factory()->forCollection($collection)->withOrder(2)->create();
        $image3 = CollectionImage::factory()->forCollection($collection)->withOrder(3)->create();

        $response = $this->patchJson(route('collection-image.moveDown', $image1->id));

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
        $collection = Collection::factory()->create();
        $image1 = CollectionImage::factory()->forCollection($collection)->withOrder(1)->create();
        $image2 = CollectionImage::factory()->forCollection($collection)->withOrder(2)->create();
        $image3 = CollectionImage::factory()->forCollection($collection)->withOrder(3)->create();

        $response = $this->patchJson(route('collection-image.moveDown', $image3->id));

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
        $collection = Collection::factory()->create();
        $image1 = CollectionImage::factory()->forCollection($collection)->withOrder(1)->create();
        $image5 = CollectionImage::factory()->forCollection($collection)->withOrder(5)->create();
        $image10 = CollectionImage::factory()->forCollection($collection)->withOrder(10)->create();
        $image15 = CollectionImage::factory()->forCollection($collection)->withOrder(15)->create();

        $response = $this->patchJson(route('collection-image.tightenOrdering', $image1->id));

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

    public function test_tighten_ordering_only_affects_same_collection(): void
    {
        $collection1 = Collection::factory()->create();
        $collection2 = Collection::factory()->create();

        $collection1Image1 = CollectionImage::factory()->forCollection($collection1)->withOrder(1)->create();
        $collection1Image5 = CollectionImage::factory()->forCollection($collection1)->withOrder(5)->create();
        $collection2Image1 = CollectionImage::factory()->forCollection($collection2)->withOrder(1)->create();
        $collection2Image10 = CollectionImage::factory()->forCollection($collection2)->withOrder(10)->create();

        $response = $this->patchJson(route('collection-image.tightenOrdering', $collection1Image1->id));

        $response->assertOk();

        // Refresh models
        $collection1Image1->refresh();
        $collection1Image5->refresh();
        $collection2Image1->refresh();
        $collection2Image10->refresh();

        // Only collection1's images should be tightened
        $this->assertEquals(1, $collection1Image1->display_order);
        $this->assertEquals(2, $collection1Image5->display_order);
        $this->assertEquals(1, $collection2Image1->display_order); // Unchanged
        $this->assertEquals(10, $collection2Image10->display_order); // Unchanged
    }

    public function test_move_operations_only_affect_same_collection(): void
    {
        $collection1 = Collection::factory()->create();
        $collection2 = Collection::factory()->create();

        $collection1Image1 = CollectionImage::factory()->forCollection($collection1)->withOrder(1)->create();
        $collection1Image2 = CollectionImage::factory()->forCollection($collection1)->withOrder(2)->create();
        $collection2Image1 = CollectionImage::factory()->forCollection($collection2)->withOrder(1)->create();
        $collection2Image2 = CollectionImage::factory()->forCollection($collection2)->withOrder(2)->create();

        $response = $this->patchJson(route('collection-image.moveDown', $collection1Image1->id));

        $response->assertOk();

        // Refresh models
        $collection1Image1->refresh();
        $collection1Image2->refresh();
        $collection2Image1->refresh();
        $collection2Image2->refresh();

        // Only collection1's images should be affected
        $this->assertEquals(2, $collection1Image1->display_order);
        $this->assertEquals(1, $collection1Image2->display_order);
        $this->assertEquals(1, $collection2Image1->display_order); // Unchanged
        $this->assertEquals(2, $collection2Image2->display_order); // Unchanged
    }

    public function test_move_up_returns_correct_structure(): void
    {
        $collection = Collection::factory()->create();
        $image1 = CollectionImage::factory()->forCollection($collection)->withOrder(1)->create();
        $image2 = CollectionImage::factory()->forCollection($collection)->withOrder(2)->create();

        $response = $this->patchJson(route('collection-image.moveUp', $image2->id));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'collection_id',
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
        $response = $this->patchJson(route('collection-image.moveUp', 'nonexistent-uuid'));
        $response->assertNotFound();

        $response = $this->patchJson(route('collection-image.moveDown', 'nonexistent-uuid'));
        $response->assertNotFound();

        $response = $this->patchJson(route('collection-image.tightenOrdering', 'nonexistent-uuid'));
        $response->assertNotFound();
    }

    public function test_single_image_move_operations_have_no_effect(): void
    {
        $collectionImage = CollectionImage::factory()->withOrder(1)->create();

        $response = $this->patchJson(route('collection-image.moveUp', $collectionImage->id));
        $response->assertOk();

        $collectionImage->refresh();
        $this->assertEquals(1, $collectionImage->display_order);

        $response = $this->patchJson(route('collection-image.moveDown', $collectionImage->id));
        $response->assertOk();

        $collectionImage->refresh();
        $this->assertEquals(1, $collectionImage->display_order);
    }

    public function test_tighten_ordering_with_single_image(): void
    {
        $collectionImage = CollectionImage::factory()->withOrder(5)->create();

        $response = $this->patchJson(route('collection-image.tightenOrdering', $collectionImage->id));

        $response->assertOk();

        $collectionImage->refresh();
        $this->assertEquals(1, $collectionImage->display_order);
    }

    public function test_move_operations_handle_duplicate_orders(): void
    {
        $collection = Collection::factory()->create();
        // Create images with duplicate orders (edge case)
        $image1 = CollectionImage::factory()->forCollection($collection)->create(['display_order' => 2]);
        $image2 = CollectionImage::factory()->forCollection($collection)->create(['display_order' => 2]);
        $image3 = CollectionImage::factory()->forCollection($collection)->create(['display_order' => 3]);

        $response = $this->patchJson(route('collection-image.moveUp', $image3->id));

        $response->assertOk();

        // Should handle the duplicate orders gracefully
        $image3->refresh();
        $this->assertLessThan(3, $image3->display_order);
    }
}

<?php

namespace Tests\Feature\Api\CollectionImage;

use App\Models\Collection;
use App\Models\CollectionImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DestroyTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function test_destroy_deletes_collection_image_successfully(): void
    {
        $collectionImage = CollectionImage::factory()->create();

        $response = $this->deleteJson(route('collection-image.destroy', $collectionImage->id));

        $response->assertNoContent();
        $this->assertDatabaseMissing('collection_images', [
            'id' => $collectionImage->id,
        ]);
    }

    public function test_destroy_returns_not_found_for_nonexistent_collection_image(): void
    {
        $response = $this->deleteJson(route('collection-image.destroy', 'nonexistent-uuid'));

        $response->assertNotFound();
    }

    public function test_destroy_adjusts_display_order_of_remaining_images(): void
    {
        $collection = Collection::factory()->create();
        $image1 = CollectionImage::factory()->forCollection($collection)->withOrder(1)->create();
        $image2 = CollectionImage::factory()->forCollection($collection)->withOrder(2)->create();
        $image3 = CollectionImage::factory()->forCollection($collection)->withOrder(3)->create();
        $image4 = CollectionImage::factory()->forCollection($collection)->withOrder(4)->create();

        // Delete the second image
        $response = $this->deleteJson(route('collection-image.destroy', $image2->id));

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
        $collectionImage = CollectionImage::factory()->create();

        $response = $this->deleteJson(route('collection-image.destroy', $collectionImage->id));

        $response->assertNoContent();
        $this->assertDatabaseMissing('collection_images', [
            'id' => $collectionImage->id,
        ]);
    }

    public function test_destroy_only_affects_images_from_same_collection(): void
    {
        $collection1 = Collection::factory()->create();
        $collection2 = Collection::factory()->create();

        $collection1Image1 = CollectionImage::factory()->forCollection($collection1)->withOrder(1)->create();
        $collection1Image2 = CollectionImage::factory()->forCollection($collection1)->withOrder(2)->create();
        $collection2Image1 = CollectionImage::factory()->forCollection($collection2)->withOrder(1)->create();
        $collection2Image2 = CollectionImage::factory()->forCollection($collection2)->withOrder(2)->create();

        // Delete image from collection1
        $response = $this->deleteJson(route('collection-image.destroy', $collection1Image1->id));

        $response->assertNoContent();

        // Check that only collection1's images are affected
        $collection1Image2->refresh();
        $collection2Image1->refresh();
        $collection2Image2->refresh();

        $this->assertEquals(1, $collection1Image2->display_order); // Reordered from 2 to 1
        $this->assertEquals(1, $collection2Image1->display_order); // Unchanged
        $this->assertEquals(2, $collection2Image2->display_order); // Unchanged
    }

    public function test_destroy_handles_gaps_in_display_order(): void
    {
        $collection = Collection::factory()->create();
        $image1 = CollectionImage::factory()->forCollection($collection)->withOrder(1)->create();
        $image5 = CollectionImage::factory()->forCollection($collection)->withOrder(5)->create();
        $image10 = CollectionImage::factory()->forCollection($collection)->withOrder(10)->create();

        // Delete the middle image
        $response = $this->deleteJson(route('collection-image.destroy', $image5->id));

        $response->assertNoContent();

        // Check that remaining images are properly reordered
        $image1->refresh();
        $image10->refresh();

        $this->assertEquals(1, $image1->display_order);
        $this->assertEquals(2, $image10->display_order); // Was 10, now 2
    }

    public function test_destroy_multiple_images_in_sequence(): void
    {
        $collection = Collection::factory()->create();
        $images = collect();

        for ($i = 1; $i <= 5; $i++) {
            $images->push(CollectionImage::factory()->forCollection($collection)->withOrder($i)->create());
        }

        // Delete images 2 and 4
        $this->deleteJson(route('collection-image.destroy', $images[1]->id))->assertNoContent();
        $this->deleteJson(route('collection-image.destroy', $images[3]->id))->assertNoContent();

        // Check that remaining images are properly reordered
        $images[0]->refresh(); // Was order 1
        $images[2]->refresh(); // Was order 3
        $images[4]->refresh(); // Was order 5

        $this->assertEquals(1, $images[0]->display_order);
        $this->assertEquals(2, $images[2]->display_order);
        $this->assertEquals(3, $images[4]->display_order);

        // Verify only 3 images remain
        $this->assertEquals(3, $collection->collectionImages()->count());
    }

    public function test_destroy_last_image_of_collection(): void
    {
        $collection = Collection::factory()->create();
        $collectionImage = CollectionImage::factory()->forCollection($collection)->withOrder(1)->create();

        $response = $this->deleteJson(route('collection-image.destroy', $collectionImage->id));

        $response->assertNoContent();
        $this->assertEquals(0, $collection->collectionImages()->count());
        $this->assertDatabaseMissing('collection_images', [
            'id' => $collectionImage->id,
        ]);
    }

    public function test_destroy_preserves_other_collection_relationships(): void
    {
        $collection = Collection::factory()->create();
        $collectionImage = CollectionImage::factory()->forCollection($collection)->create();

        // Ensure the collection still exists after deleting its image
        $response = $this->deleteJson(route('collection-image.destroy', $collectionImage->id));

        $response->assertNoContent();
        $this->assertDatabaseHas('collections', [
            'id' => $collection->id,
        ]);
    }
}

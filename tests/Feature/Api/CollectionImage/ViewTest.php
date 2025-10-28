<?php

namespace Tests\Feature\Api\CollectionImage;

use App\Enums\Permission;
use App\Models\Collection;
use App\Models\CollectionImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class ViewTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWith([Permission::VIEW_DATA->value]);
        $this->actingAs($this->user);

        // Set up a fake storage disk for testing
        Storage::fake('local');
        // CollectionImages share disk with AvailableImages
        config(['localstorage.available.images.disk' => 'local']);
    }

    public function test_view_returns_image_file(): void
    {
        // Create a test image file
        $imagePath = 'images/collections/test-image.jpg';
        Storage::disk('local')->put($imagePath, file_get_contents(__DIR__.'/../../../fixtures/test-image.jpg'));

        // Create CollectionImage with the test file path
        $collectionImage = CollectionImage::factory()->create([
            'path' => $imagePath,
            'mime_type' => 'image/jpeg',
        ]);

        $response = $this->get(route('collection-image.view', $collectionImage->id));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_view_returns_404_if_image_file_does_not_exist(): void
    {
        // Create CollectionImage with a non-existent file path
        $collectionImage = CollectionImage::factory()->create([
            'path' => 'images/collections/non-existent.jpg',
        ]);

        $response = $this->get(route('collection-image.view', $collectionImage->id));

        $response->assertNotFound();
    }

    public function test_download_returns_image_file_as_attachment(): void
    {
        // Create a test image file
        $imagePath = 'images/collections/test-download.jpg';
        Storage::disk('local')->put($imagePath, file_get_contents(__DIR__.'/../../../fixtures/test-image.jpg'));

        // Create CollectionImage with the test file path
        $collectionImage = CollectionImage::factory()->create([
            'path' => $imagePath,
            'original_name' => 'original-name.jpg',
            'mime_type' => 'image/jpeg',
        ]);

        $response = $this->get(route('collection-image.download', $collectionImage->id));

        $response->assertOk();
        $response->assertDownload('original-name.jpg');
    }

    public function test_index_returns_collection_images_as_collection(): void
    {
        $collection = Collection::factory()->create();

        // Create multiple images for the collection
        $image1 = CollectionImage::factory()->create([
            'collection_id' => $collection->id,
            'display_order' => 1,
        ]);
        $image2 = CollectionImage::factory()->create([
            'collection_id' => $collection->id,
            'display_order' => 2,
        ]);

        $response = $this->getJson(route('collection.images.index', $collection->id));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
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
            ],
        ]);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_index_orders_images_by_display_order(): void
    {
        $collection = Collection::factory()->create();

        // Create images in reverse order
        $image3 = CollectionImage::factory()->create([
            'collection_id' => $collection->id,
            'display_order' => 3,
        ]);
        $image1 = CollectionImage::factory()->create([
            'collection_id' => $collection->id,
            'display_order' => 1,
        ]);
        $image2 = CollectionImage::factory()->create([
            'collection_id' => $collection->id,
            'display_order' => 2,
        ]);

        $response = $this->getJson(route('collection.images.index', $collection->id));

        $response->assertOk();
        $data = $response->json('data');
        $this->assertEquals(1, $data[0]['display_order']);
        $this->assertEquals(2, $data[1]['display_order']);
        $this->assertEquals(3, $data[2]['display_order']);
    }
}

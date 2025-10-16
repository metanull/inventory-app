<?php

namespace Tests\Feature\Api\ItemImage;

use App\Models\Item;
use App\Models\ItemImage;
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
        $this->user = $this->createVisitorUser();
        $this->actingAs($this->user);

        // Set up a fake storage disk for testing
        Storage::fake('local');
    }

    public function test_view_returns_image_file(): void
    {
        // Create a test image file
        $imagePath = 'images/items/test-image.jpg';
        Storage::disk('local')->put($imagePath, file_get_contents(__DIR__.'/../../../fixtures/test-image.jpg'));

        // Create ItemImage with the test file path
        $itemImage = ItemImage::factory()->create([
            'path' => $imagePath,
            'mime_type' => 'image/jpeg',
        ]);

        $response = $this->get(route('item-image.view', $itemImage->id));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_view_returns_404_if_image_file_does_not_exist(): void
    {
        // Create ItemImage with a non-existent file path
        $itemImage = ItemImage::factory()->create([
            'path' => 'images/items/non-existent.jpg',
        ]);

        $response = $this->get(route('item-image.view', $itemImage->id));

        $response->assertNotFound();
    }

    public function test_download_returns_image_file_as_attachment(): void
    {
        // Create a test image file
        $imagePath = 'images/items/test-download.jpg';
        Storage::disk('local')->put($imagePath, file_get_contents(__DIR__.'/../../../fixtures/test-image.jpg'));

        // Create ItemImage with the test file path
        $itemImage = ItemImage::factory()->create([
            'path' => $imagePath,
            'original_name' => 'original-name.jpg',
            'mime_type' => 'image/jpeg',
        ]);

        $response = $this->get(route('item-image.download', $itemImage->id));

        $response->assertOk();
        $response->assertDownload('original-name.jpg');
    }

    public function test_index_returns_item_images_as_collection(): void
    {
        $item = Item::factory()->create();

        // Create multiple images for the item
        $image1 = ItemImage::factory()->create([
            'item_id' => $item->id,
            'display_order' => 1,
        ]);
        $image2 = ItemImage::factory()->create([
            'item_id' => $item->id,
            'display_order' => 2,
        ]);

        $response = $this->getJson(route('item.images.index', $item->id));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
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
            ],
        ]);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_index_orders_images_by_display_order(): void
    {
        $item = Item::factory()->create();

        // Create images in reverse order
        $image3 = ItemImage::factory()->create([
            'item_id' => $item->id,
            'display_order' => 3,
        ]);
        $image1 = ItemImage::factory()->create([
            'item_id' => $item->id,
            'display_order' => 1,
        ]);
        $image2 = ItemImage::factory()->create([
            'item_id' => $item->id,
            'display_order' => 2,
        ]);

        $response = $this->getJson(route('item.images.index', $item->id));

        $response->assertOk();
        $data = $response->json('data');
        $this->assertEquals(1, $data[0]['display_order']);
        $this->assertEquals(2, $data[1]['display_order']);
        $this->assertEquals(3, $data[2]['display_order']);
    }
}

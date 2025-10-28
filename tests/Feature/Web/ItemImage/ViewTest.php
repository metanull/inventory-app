<?php

namespace Tests\Feature\Web\ItemImage;

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

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsDataUser();

        // Set up a fake storage disk for testing
        Storage::fake('local');
        config(['localstorage.pictures.disk' => 'local']);
    }

    public function test_view_returns_image_file(): void
    {
        $item = Item::factory()->create();

        // Create a test image file
        $imagePath = 'images/items/test-image.jpg';
        Storage::disk('local')->put($imagePath, file_get_contents(__DIR__.'/../../../fixtures/test-image.jpg'));

        // Create ItemImage with the test file path
        $itemImage = ItemImage::factory()->create([
            'item_id' => $item->id,
            'path' => $imagePath,
            'mime_type' => 'image/jpeg',
            'display_order' => 1,
        ]);

        $response = $this->get(route('items.item-images.view', [$item, $itemImage]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/jpeg');
    }

    public function test_view_returns_404_if_image_file_does_not_exist(): void
    {
        $item = Item::factory()->create();

        // Create ItemImage with a non-existent file path
        $itemImage = ItemImage::factory()->create([
            'item_id' => $item->id,
            'path' => 'images/items/non-existent.jpg',
            'display_order' => 1,
        ]);

        $response = $this->get(route('items.item-images.view', [$item, $itemImage]));

        $response->assertNotFound();
    }

    public function test_view_returns_404_if_image_belongs_to_different_item(): void
    {
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();

        // Create ItemImage for item1
        $itemImage = ItemImage::factory()->create([
            'item_id' => $item1->id,
            'path' => 'images/items/test.jpg',
            'display_order' => 1,
        ]);

        // Try to access via item2's route
        $response = $this->get(route('items.item-images.view', [$item2, $itemImage]));

        $response->assertNotFound();
    }

    public function test_view_requires_authentication(): void
    {
        // Start as unauthenticated
        $item = Item::factory()->create();
        $itemImage = ItemImage::factory()->create([
            'item_id' => $item->id,
            'display_order' => 1,
        ]);

        // Make request without authentication
        $this->app['auth']->forgetGuards();
        $response = $this->get(route('items.item-images.view', [$item, $itemImage]));

        $response->assertRedirect(route('login'));
    }

    public function test_view_requires_view_data_permission(): void
    {
        // Create a user without any permissions
        $user = User::factory()->create();
        $this->actingAs($user);

        $item = Item::factory()->create();
        $itemImage = ItemImage::factory()->create([
            'item_id' => $item->id,
            'display_order' => 1,
        ]);

        $response = $this->get(route('items.item-images.view', [$item, $itemImage]));

        $response->assertForbidden();
    }

    public function test_download_returns_image_file_as_attachment(): void
    {
        $item = Item::factory()->create();

        // Create a test image file
        $imagePath = 'images/items/test-download.jpg';
        Storage::disk('local')->put($imagePath, file_get_contents(__DIR__.'/../../../fixtures/test-image.jpg'));

        // Create ItemImage with the test file path
        $itemImage = ItemImage::factory()->create([
            'item_id' => $item->id,
            'path' => $imagePath,
            'original_name' => 'original-name.jpg',
            'mime_type' => 'image/jpeg',
            'display_order' => 1,
        ]);

        $response = $this->get(route('items.item-images.download', [$item, $itemImage]));

        $response->assertOk();
        $response->assertDownload('original-name.jpg');
    }
}

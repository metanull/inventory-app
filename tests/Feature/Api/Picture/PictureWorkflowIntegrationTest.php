<?php

namespace Tests\Feature\Api\Picture;

use App\Models\AvailableImage;
use App\Models\Item;
use App\Models\Picture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PictureWorkflowIntegrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_available_image_to_picture_file_transition(): void
    {
        // Create an item and available image
        $item = Item::factory()->create();
        $availableImage = AvailableImage::factory()->create([
            'path' => 'images/workflow-test.jpg',
        ]);

        // Mock storage and create the file in available images directory
        Storage::fake('public');
        $originalContent = 'fake-image-content-for-workflow-test';
        Storage::disk('public')->put($availableImage->path, $originalContent);

        // Verify file exists in available images directory
        $this->assertTrue(Storage::disk('public')->exists('images/workflow-test.jpg'));
        $this->assertFalse(Storage::disk('public')->exists('pictures/workflow-test.jpg'));

        // Attach the available image to the item
        $response = $this->postJson(route('picture.attachToItem', $item), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Workflow Test Picture',
            'copyright_text' => 'Test Copyright',
        ]);

        $response->assertCreated();

        // Verify file was moved from available images to pictures directory
        $this->assertFalse(Storage::disk('public')->exists('images/workflow-test.jpg'));
        $this->assertTrue(Storage::disk('public')->exists('pictures/workflow-test.jpg'));

        // Verify file content is preserved
        $this->assertEquals($originalContent, Storage::disk('public')->get('pictures/workflow-test.jpg'));

        // Verify database state: AvailableImage deleted, Picture created
        $this->assertDatabaseMissing('available_images', [
            'id' => $availableImage->id,
        ]);

        $this->assertDatabaseHas('pictures', [
            'internal_name' => 'Workflow Test Picture',
            'copyright_text' => 'Test Copyright',
            'path' => 'pictures/workflow-test.jpg',
            'upload_name' => 'workflow-test.jpg',
            'upload_extension' => 'jpg',
            'pictureable_type' => 'App\\Models\\Item',
            'pictureable_id' => $item->id,
        ]);

        // Verify item relationship
        $this->assertEquals(1, $item->fresh()->pictures()->count());
        $picture = $item->fresh()->pictures()->first();
        $this->assertEquals('Workflow Test Picture', $picture->internal_name);
    }

    public function test_picture_to_available_image_file_transition(): void
    {
        // Create an item with a picture
        $item = Item::factory()->create();
        $picture = Picture::factory()->forItem()->create([
            'pictureable_id' => $item->id,
            'pictureable_type' => get_class($item),
            'path' => 'pictures/detach-test.png',
            'upload_name' => 'detach-test.png',
            'internal_name' => 'Detach Test Picture',
        ]);

        // Mock storage and create the file in pictures directory
        Storage::fake('public');
        $originalContent = 'fake-picture-content-for-detach-test';
        Storage::disk('public')->put($picture->path, $originalContent);

        // Verify file exists in pictures directory
        $this->assertTrue(Storage::disk('public')->exists('pictures/detach-test.png'));
        $this->assertFalse(Storage::disk('public')->exists('images/detach-test.png'));

        // Detach the picture from the item
        $response = $this->deleteJson(route('picture.detachFromItem', [$item, $picture]), [
            'comment' => 'Detached for workflow test',
        ]);

        $response->assertOk();

        // Verify file was moved from pictures to available images directory
        $this->assertFalse(Storage::disk('public')->exists('pictures/detach-test.png'));
        $this->assertTrue(Storage::disk('public')->exists('images/detach-test.png'));

        // Verify file content is preserved
        $this->assertEquals($originalContent, Storage::disk('public')->get('images/detach-test.png'));

        // Verify database state: Picture deleted, AvailableImage created
        $this->assertDatabaseMissing('pictures', [
            'id' => $picture->id,
        ]);

        $this->assertDatabaseHas('available_images', [
            'path' => 'images/detach-test.png',
            'comment' => 'Detached for workflow test',
        ]);

        // Verify item relationship
        $this->assertEquals(0, $item->fresh()->pictures()->count());

        // Verify the new AvailableImage was created correctly
        $availableImage = AvailableImage::where('path', 'images/detach-test.png')->first();
        $this->assertNotNull($availableImage);
        $this->assertEquals('Detached for workflow test', $availableImage->comment);
    }

    public function test_complete_attach_detach_cycle(): void
    {
        // Create an item and available image
        $item = Item::factory()->create();
        $availableImage = AvailableImage::factory()->create([
            'path' => 'images/cycle-test.gif',
            'comment' => 'Original available image',
        ]);

        // Mock storage and create the file
        Storage::fake('public');
        $originalContent = 'fake-gif-content-for-cycle-test';
        Storage::disk('public')->put($availableImage->path, $originalContent);

        // STEP 1: Attach AvailableImage to Item (creating Picture)
        $attachResponse = $this->postJson(route('picture.attachToItem', $item), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Cycle Test Picture',
            'copyright_text' => 'Cycle Test Copyright',
        ]);

        $attachResponse->assertCreated();

        // Verify attachment state
        $this->assertFalse(Storage::disk('public')->exists('images/cycle-test.gif'));
        $this->assertTrue(Storage::disk('public')->exists('pictures/cycle-test.gif'));
        $this->assertEquals($originalContent, Storage::disk('public')->get('pictures/cycle-test.gif'));

        $this->assertDatabaseMissing('available_images', ['id' => $availableImage->id]);
        $this->assertDatabaseHas('pictures', [
            'internal_name' => 'Cycle Test Picture',
            'path' => 'pictures/cycle-test.gif',
            'pictureable_id' => $item->id,
        ]);

        // Get the created picture
        $picture = Picture::where('internal_name', 'Cycle Test Picture')->first();
        $this->assertNotNull($picture);

        // STEP 2: Detach Picture from Item (creating new AvailableImage)
        $detachResponse = $this->deleteJson(route('picture.detachFromItem', [$item, $picture]), [
            'comment' => 'Cycled back to available',
        ]);

        $detachResponse->assertOk();

        // Verify detachment state
        $this->assertTrue(Storage::disk('public')->exists('images/cycle-test.gif'));
        $this->assertFalse(Storage::disk('public')->exists('pictures/cycle-test.gif'));
        $this->assertEquals($originalContent, Storage::disk('public')->get('images/cycle-test.gif'));

        $this->assertDatabaseMissing('pictures', ['id' => $picture->id]);
        $this->assertDatabaseHas('available_images', [
            'path' => 'images/cycle-test.gif',
            'comment' => 'Cycled back to available',
        ]);

        // Verify the file content was preserved throughout the cycle
        $this->assertEquals($originalContent, Storage::disk('public')->get('images/cycle-test.gif'));

        // Verify item has no pictures
        $this->assertEquals(0, $item->fresh()->pictures()->count());

        // Verify we have a new AvailableImage (different ID from original)
        $newAvailableImage = AvailableImage::where('path', 'images/cycle-test.gif')->first();
        $this->assertNotNull($newAvailableImage);
        $this->assertNotEquals($availableImage->id, $newAvailableImage->id);
        $this->assertEquals('Cycled back to available', $newAvailableImage->comment);
    }

    public function test_file_integrity_across_multiple_transitions(): void
    {
        // Create test data
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();
        $availableImage = AvailableImage::factory()->create([
            'path' => 'images/integrity-test.jpg',
        ]);

        // Mock storage with specific content to verify integrity
        Storage::fake('public');
        $originalContent = 'JPEG-like-binary-content-for-integrity-test-12345';
        Storage::disk('public')->put($availableImage->path, $originalContent);

        // Step 1: Attach to first item
        $response1 = $this->postJson(route('picture.attachToItem', $item1), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Integrity Test Picture',
        ]);
        $response1->assertCreated();
        $picture1 = Picture::where('internal_name', 'Integrity Test Picture')->first();

        // Verify file integrity after first transition
        $this->assertEquals($originalContent, Storage::disk('public')->get('pictures/integrity-test.jpg'));

        // Step 2: Detach from first item
        $response2 = $this->deleteJson(route('picture.detachFromItem', [$item1, $picture1]));
        $response2->assertOk();
        $newAvailableImage = AvailableImage::where('path', 'images/integrity-test.jpg')->first();

        // Verify file integrity after detachment
        $this->assertEquals($originalContent, Storage::disk('public')->get('images/integrity-test.jpg'));

        // Step 3: Attach to second item
        $response3 = $this->postJson(route('picture.attachToItem', $item2), [
            'available_image_id' => $newAvailableImage->id,
            'internal_name' => 'Integrity Test Picture 2',
        ]);
        $response3->assertCreated();

        // Verify file integrity after second attachment
        $this->assertEquals($originalContent, Storage::disk('public')->get('pictures/integrity-test.jpg'));

        // Verify final state
        $this->assertEquals(0, $item1->fresh()->pictures()->count());
        $this->assertEquals(1, $item2->fresh()->pictures()->count());
        $this->assertDatabaseMissing('available_images', ['id' => $newAvailableImage->id]);
    }

    public function test_storage_disk_configuration_respected(): void
    {
        // This test verifies that the correct storage disks and directories are used
        $item = Item::factory()->create();
        $availableImage = AvailableImage::factory()->create([
            'path' => 'images/config-test.jpg',
        ]);

        Storage::fake('public');
        Storage::disk('public')->put($availableImage->path, 'test-content');

        // Verify configuration values are being used correctly
        $availableImagesDir = config('localstorage.available.images.directory', 'images');
        $picturesDir = config('localstorage.pictures.directory', 'pictures');

        $this->assertEquals('images', $availableImagesDir);
        $this->assertEquals('pictures', $picturesDir);

        // Test attachment respects configuration
        $response = $this->postJson(route('picture.attachToItem', $item), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Config Test Picture',
        ]);

        $response->assertCreated();
        $picture = Picture::where('internal_name', 'Config Test Picture')->first();

        // Verify the path uses the configured directory
        $this->assertStringStartsWith($picturesDir.'/', $picture->path);
        $this->assertTrue(Storage::disk('public')->exists($picture->path));
    }

    private function withoutAuthentication(): void
    {
        $this->user = null;
        $this->app['auth']->forgetGuards();
    }
}

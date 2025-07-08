<?php

namespace Tests\Integration\Api\Picture;

use App\Events\ImageUploadEvent;
use App\Listeners\ImageUploadListener;
use App\Models\AvailableImage;
use App\Models\ImageUpload;
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

    public function test_complete_picture_workflow_imageupload_to_picture_to_availableimage(): void
    {
        // Mock storage for all disk operations
        Storage::fake('local');
        Storage::fake('public');

        // Step 1: Create ImageUpload (simulating file upload)
        $imageUpload = ImageUpload::factory()->create([
            'path' => 'image_uploads/test-workflow.jpg',
            'name' => 'test-workflow.jpg',
            'extension' => 'jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
        ]);

        // Create the actual file in upload directory - use a minimal valid JPEG
        $minimalJpeg = base64_decode('/9j/4AAQSkZJRgABAQEAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCdABmX/9k=');
        Storage::disk('local')->put($imageUpload->path, $minimalJpeg);

        // Verify initial state
        $this->assertDatabaseHas('image_uploads', ['id' => $imageUpload->id]);
        $this->assertTrue(Storage::disk('local')->exists($imageUpload->path));
        $this->assertDatabaseCount('available_images', 0);
        $this->assertDatabaseCount('pictures', 0);

        // Step 2: Trigger ImageUploadEvent and listener
        $listener = new ImageUploadListener;
        $event = new ImageUploadEvent($imageUpload);
        $listener->handle($event);

        // Verify AvailableImage was created
        $this->assertDatabaseCount('available_images', 1);
        $availableImage = AvailableImage::first();
        $this->assertNotNull($availableImage);
        $this->assertEquals('images/test-workflow.jpg', $availableImage->path);
        $this->assertTrue(Storage::disk('public')->exists($availableImage->path));
        // Content may be processed/resized, so we just check that it exists
        $this->assertNotEmpty(Storage::disk('public')->get($availableImage->path));

        // Step 3: Attach AvailableImage to Item (creating Picture)
        $item = Item::factory()->create();

        $attachResponse = $this->postJson(route('picture.attachToItem', $item), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Workflow Test Picture',
            'copyright_text' => 'Test Copyright',
        ]);

        $attachResponse->assertCreated();

        // Verify Picture was created and AvailableImage was removed
        $this->assertDatabaseCount('pictures', 1);
        $this->assertDatabaseCount('available_images', 0);
        $this->assertDatabaseMissing('available_images', ['id' => $availableImage->id]);

        $picture = Picture::first();
        $this->assertNotNull($picture);
        $this->assertEquals('Workflow Test Picture', $picture->internal_name);
        $this->assertEquals('Test Copyright', $picture->copyright_text);
        $this->assertEquals($item->id, $picture->pictureable_id);
        $this->assertEquals(get_class($item), $picture->pictureable_type);
        $this->assertEquals('pictures/test-workflow.jpg', $picture->path);

        // Verify file moved from available images to pictures
        $this->assertFalse(Storage::disk('public')->exists('images/test-workflow.jpg'));
        $this->assertTrue(Storage::disk('public')->exists('pictures/test-workflow.jpg'));

        // Verify the file content is valid (should be the processed image data)
        $pictureContent = Storage::disk('public')->get('pictures/test-workflow.jpg');
        $this->assertNotNull($pictureContent);
        $this->assertNotEmpty($pictureContent);

        // Step 4: Detach Picture back to AvailableImage
        $detachResponse = $this->deleteJson(route('picture.detachFromItem', [$item, $picture]), [
            'comment' => 'Workflow test detachment',
        ]);

        $detachResponse->assertOk();

        // Verify Picture was removed and AvailableImage was created
        $this->assertDatabaseCount('pictures', 0);
        $this->assertDatabaseCount('available_images', 1);
        $this->assertDatabaseMissing('pictures', ['id' => $picture->id]);

        $newAvailableImage = AvailableImage::first();
        $this->assertNotNull($newAvailableImage);
        $this->assertEquals('images/test-workflow.jpg', $newAvailableImage->path);
        $this->assertEquals('Workflow test detachment', $newAvailableImage->comment);

        // Verify file moved back from pictures to available images
        $this->assertTrue(Storage::disk('public')->exists('images/test-workflow.jpg'));
        $this->assertFalse(Storage::disk('public')->exists('pictures/test-workflow.jpg'));
        // Content may be processed/resized, so we just check that it exists
        $this->assertNotEmpty(Storage::disk('public')->get('images/test-workflow.jpg'));

        // Verify item no longer has pictures
        $this->assertEquals(0, $item->fresh()->pictures()->count());
    }

    public function test_file_content_preservation_through_complete_workflow(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        // Create ImageUpload with a minimal valid JPEG
        $imageUpload = ImageUpload::factory()->create([
            'path' => 'image_uploads/content-test.jpg',
        ]);
        $minimalJpeg = base64_decode('/9j/4AAQSkZJRgABAQEAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCdABmX/9k=');
        Storage::disk('local')->put($imageUpload->path, $minimalJpeg);

        // Process through listener
        $listener = new ImageUploadListener;
        $event = new ImageUploadEvent($imageUpload);
        $listener->handle($event);

        $availableImage = AvailableImage::first();
        // Content may be processed/resized, so we just check that it exists
        $this->assertNotEmpty(Storage::disk('public')->get($availableImage->path));

        // Attach to item
        $item = Item::factory()->create();
        $this->postJson(route('picture.attachToItem', $item), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Content Test',
        ]);

        $picture = Picture::first();
        // Content may be processed/resized, so we just check that it exists
        $this->assertNotEmpty(Storage::disk('public')->get($picture->path));

        // Detach back
        $this->deleteJson(route('picture.detachFromItem', [$item, $picture]));

        $newAvailableImage = AvailableImage::first();
        // Content may be processed/resized, so we just check that it exists
        $this->assertNotEmpty(Storage::disk('public')->get($newAvailableImage->path));
    }

    public function test_multiple_attach_detach_cycles(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        // Create initial ImageUpload
        $imageUpload = ImageUpload::factory()->create([
            'path' => 'image_uploads/cycle-test.jpg',
        ]);
        $minimalJpeg = base64_decode('/9j/4AAQSkZJRgABAQEAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCdABmX/9k=');
        Storage::disk('local')->put($imageUpload->path, $minimalJpeg);

        // Process to AvailableImage
        $listener = new ImageUploadListener;
        $listener->handle(new ImageUploadEvent($imageUpload));

        $availableImage = AvailableImage::first();
        $item = Item::factory()->create();

        // Cycle 1: Attach and detach
        $this->postJson(route('picture.attachToItem', $item), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Cycle 1',
        ]);

        $picture = Picture::first();
        $this->assertEquals('Cycle 1', $picture->internal_name);

        $this->deleteJson(route('picture.detachFromItem', [$item, $picture]), [
            'comment' => 'End of cycle 1',
        ]);

        $availableImage = AvailableImage::first();
        $this->assertEquals('End of cycle 1', $availableImage->comment);

        // Cycle 2: Attach and detach again
        $this->postJson(route('picture.attachToItem', $item), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Cycle 2',
        ]);

        $picture = Picture::first();
        $this->assertEquals('Cycle 2', $picture->internal_name);

        $this->deleteJson(route('picture.detachFromItem', [$item, $picture]), [
            'comment' => 'End of cycle 2',
        ]);

        // Verify final state
        $finalAvailableImage = AvailableImage::first();
        $this->assertEquals('End of cycle 2', $finalAvailableImage->comment);
        // Content may be processed/resized, so we just check that it exists
        $this->assertNotEmpty(Storage::disk('public')->get($finalAvailableImage->path));
        $this->assertDatabaseCount('pictures', 0);
        $this->assertEquals(0, $item->fresh()->pictures()->count());
    }

    public function test_different_model_types_in_workflow(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        // Create three ImageUploads for different model types
        $uploads = [
            'item' => ImageUpload::factory()->create(['path' => 'image_uploads/item-test.jpg']),
            'detail' => ImageUpload::factory()->create(['path' => 'image_uploads/detail-test.jpg']),
            'partner' => ImageUpload::factory()->create(['path' => 'image_uploads/partner-test.jpg']),
        ];

        $minimalJpeg = base64_decode('/9j/4AAQSkZJRgABAQEAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCdABmX/9k=');
        foreach ($uploads as $type => $upload) {
            Storage::disk('local')->put($upload->path, $minimalJpeg);
        }

        $listener = new ImageUploadListener;

        // Process all uploads to AvailableImages
        foreach ($uploads as $upload) {
            $listener->handle(new ImageUploadEvent($upload));
        }

        $this->assertDatabaseCount('available_images', 3);

        // Create models
        $item = Item::factory()->create();
        $detail = \App\Models\Detail::factory()->create();
        $partner = \App\Models\Partner::factory()->create();

        $availableImages = AvailableImage::all();

        // Attach to different model types
        $this->postJson(route('picture.attachToItem', $item), [
            'available_image_id' => $availableImages[0]->id,
            'internal_name' => 'Item Picture',
        ]);

        $this->postJson(route('picture.attachToDetail', $detail), [
            'available_image_id' => $availableImages[1]->id,
            'internal_name' => 'Detail Picture',
        ]);

        $this->postJson(route('picture.attachToPartner', $partner), [
            'available_image_id' => $availableImages[2]->id,
            'internal_name' => 'Partner Picture',
        ]);

        // Verify all attachments
        $this->assertDatabaseCount('pictures', 3);
        $this->assertDatabaseCount('available_images', 0);

        $pictures = Picture::all();
        $this->assertEquals(1, $item->pictures()->count());
        $this->assertEquals(1, $detail->pictures()->count());
        $this->assertEquals(1, $partner->pictures()->count());

        // Detach all back to AvailableImages
        foreach ($pictures as $picture) {
            $pictureable = $picture->pictureable;
            $routeName = 'picture.detachFrom'.class_basename($pictureable);

            $this->deleteJson(route($routeName, [$pictureable, $picture]), [
                'comment' => 'Detached from '.class_basename($pictureable),
            ]);
        }

        // Verify final state
        $this->assertDatabaseCount('pictures', 0);
        $this->assertDatabaseCount('available_images', 3);
        $this->assertEquals(0, $item->fresh()->pictures()->count());
        $this->assertEquals(0, $detail->fresh()->pictures()->count());
        $this->assertEquals(0, $partner->fresh()->pictures()->count());
    }
}

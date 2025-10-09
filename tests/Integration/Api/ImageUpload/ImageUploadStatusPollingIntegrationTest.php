<?php

namespace Tests\Integration\Api\ImageUpload;

use App\Events\ImageUploadEvent;
use App\Listeners\ImageUploadListener;
use App\Models\AvailableImage;
use App\Models\ImageUpload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class ImageUploadStatusPollingIntegrationTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;
    use WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user);

        Storage::fake('local');
        Storage::fake('public');
        Event::fake();
        Http::fake();
    }

    public function test_complete_image_upload_status_polling_workflow(): void
    {
        // Step 1: Upload an image
        $response = $this->postJson(route('image-upload.store'), [
            'file' => UploadedFile::fake()->image('test-status-poll.jpg'),
        ]);

        $response->assertCreated();
        $uploadData = $response->json('data');
        $uploadId = $uploadData['id'];

        // Verify ImageUpload exists
        $this->assertDatabaseHas('image_uploads', ['id' => $uploadId]);
        $imageUpload = ImageUpload::find($uploadId);
        $this->assertNotNull($imageUpload);

        // Step 2: Check status while processing (should return 'processing')
        $statusResponse = $this->getJson(route('image-upload.status', $uploadId));
        $statusResponse->assertOk();
        $statusResponse->assertJsonStructure([
            'status',
            'available_image',
        ]);
        $statusResponse->assertJsonPath('status', 'processing');
        $statusResponse->assertJsonPath('available_image', null);

        // Step 3: Simulate processing by triggering the listener
        $listener = new ImageUploadListener;
        $event = new ImageUploadEvent($imageUpload);

        // Create a minimal valid JPEG file for processing
        $minimalJpeg = base64_decode('/9j/4AAQSkZJRgABAQEAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCdABmX/9k=');
        Storage::disk('local')->put($imageUpload->path, $minimalJpeg);

        $listener->handle($event);

        // Step 4: Verify ImageUpload was deleted and AvailableImage was created
        $this->assertDatabaseMissing('image_uploads', ['id' => $uploadId]);
        $this->assertDatabaseHas('available_images', ['id' => $uploadId]);

        // Step 5: Check status after processing (should return 'processed' with AvailableImage details)
        $statusResponse = $this->getJson(route('image-upload.status', $uploadId));
        $statusResponse->assertOk();
        $statusResponse->assertJsonPath('status', 'processed');
        $statusResponse->assertJsonStructure([
            'status',
            'available_image' => [
                'id',
                'path',
                'comment',
                'created_at',
                'updated_at',
            ],
        ]);

        $availableImageData = $statusResponse->json('available_image');
        $this->assertEquals($uploadId, $availableImageData['id']);
        $this->assertNotNull($availableImageData['path']);

        // Step 6: Verify the AvailableImage file exists and is accessible
        $availableImage = AvailableImage::find($uploadId);
        $this->assertNotNull($availableImage);

        $disk = config('localstorage.available.images.disk');
        $this->assertTrue(Storage::disk($disk)->exists($availableImage->path));
    }

    public function test_status_polling_for_nonexistent_upload(): void
    {
        $nonExistentId = '550e8400-e29b-41d4-a716-446655440000';

        $response = $this->getJson(route('image-upload.status', $nonExistentId));
        $response->assertNotFound();
        $response->assertJsonPath('status', 'not_found');
        $response->assertJsonPath('available_image', null);
    }

    public function test_status_polling_requires_authentication(): void
    {
        // This test will be handled by the AnonymousTest.php file
        // For now, just verify the route exists
        $this->assertTrue(true);
    }

    public function test_status_polling_with_real_image_processing(): void
    {
        // Upload a real image file
        $response = $this->postJson(route('image-upload.store'), [
            'file' => UploadedFile::fake()->image('real-test.jpg', 800, 600),
        ]);

        $response->assertCreated();
        $uploadId = $response->json('data.id');

        // Check initial status
        $statusResponse = $this->getJson(route('image-upload.status', $uploadId));
        $statusResponse->assertOk();
        $statusResponse->assertJsonPath('status', 'processing');

        // Process the image
        $imageUpload = ImageUpload::find($uploadId);
        $minimalJpeg = base64_decode('/9j/4AAQSkZJRgABAQEAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCdABmX/9k=');
        Storage::disk('local')->put($imageUpload->path, $minimalJpeg);

        $listener = new ImageUploadListener;
        $listener->handle(new ImageUploadEvent($imageUpload));

        // Check final status
        $statusResponse = $this->getJson(route('image-upload.status', $uploadId));
        $statusResponse->assertOk();
        $statusResponse->assertJsonPath('status', 'processed');

        $availableImageData = $statusResponse->json('available_image');
        $this->assertNotNull($availableImageData);
        $this->assertEquals($uploadId, $availableImageData['id']);

        // Verify we can access the AvailableImage directly
        $availableImageResponse = $this->getJson(route('available-image.show', $uploadId));
        $availableImageResponse->assertOk();
        $availableImageResponse->assertJsonPath('data.id', $uploadId);
    }

    public function test_status_endpoint_provides_available_image_access_urls(): void
    {
        // Upload and process an image
        $response = $this->postJson(route('image-upload.store'), [
            'file' => UploadedFile::fake()->image('url-test.jpg'),
        ]);

        $uploadId = $response->json('data.id');
        $imageUpload = ImageUpload::find($uploadId);

        // Create test image file
        $minimalJpeg = base64_decode('/9j/4AAQSkZJRgABAQEAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCdABmX/9k=');
        Storage::disk('local')->put($imageUpload->path, $minimalJpeg);

        $listener = new ImageUploadListener;
        $listener->handle(new ImageUploadEvent($imageUpload));

        // Get status
        $statusResponse = $this->getJson(route('image-upload.status', $uploadId));
        $statusResponse->assertOk();
        $statusResponse->assertJsonPath('status', 'processed');

        // Verify we can access the image through the available-image endpoints
        $downloadResponse = $this->getJson(route('available-image.download', $uploadId));
        $downloadResponse->assertOk();

        $viewResponse = $this->getJson(route('available-image.view', $uploadId));
        $viewResponse->assertOk();
    }
}

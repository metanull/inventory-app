<?php

namespace Tests\Feature\Event\AvailableImage;

use App\Events\AvailableImageEvent;
use App\Events\ImageUploadEvent;
use App\Listeners\AvailableImageListener;
use App\Listeners\ImageUploadListener;
use App\Models\AvailableImage;
use App\Models\ImageUpload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AvailableImageTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
        Storage::disk('local')->makeDirectory('image_uploads');
        Storage::disk('public')->makeDirectory('images');
        Event::fake();
        Http::fake();
        $this->user = User::factory()->create();
    }

    public function test_availableimagelistener_listener_is_registered_for_availableimageevent_event(): void
    {
        Event::assertListening(
            expectedEvent: AvailableImageEvent::class,
            expectedListener: AvailableImageListener::class,
        );
    }

    public function test_availableimagelistener_removes_uploaded_image_from_local_disk(): void
    {
        $imageUpload = ImageUpload::factory()->create();
        // Create a valid image file since storage is faked
        $minimalJpeg = base64_decode('/9j/4AAQSkZJRgABAQEAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCdABmX/9k=');
        Storage::disk('local')->put($imageUpload->path, $minimalJpeg);

        $imageUploadEvent = new ImageUploadEvent($imageUpload);
        $imageUploadListener = new ImageUploadListener;
        $imageUploadListener->handle($imageUploadEvent);
        $this->assertFileDoesNotExist(Storage::disk('local')->path($imageUpload->path));
    }

    public function test_availableimagelistener_creates_an_image_on_the_public_disk(): void
    {
        // Create a fake image upload
        $imageUpload = ImageUpload::factory()->create();

        // Dispatch the AvailableImageEvent, using the fake uploaded image's path as source
        $availableImage = AvailableImage::factory()->create([
            'path' => $imageUpload->path,
            'id' => $imageUpload->id,
        ]);
        $availableImageEvent = new AvailableImageEvent($availableImage);
        $availableImageListener = new AvailableImageListener;
        $availableImageListener->handle($availableImageEvent);

        // The Listener should have created a new image in the public disk, and have updated
        // the path of the AvailableImage model to point to the new image in the public disk.
        $availableImage->refresh();
        $this->assertFileExists(Storage::disk('public')->path($availableImage->path));
    }

    public function test_availableimagelistener_updates_the_image_path_in_database(): void
    {
        // Create a fake image upload
        $imageUpload = ImageUpload::factory()->create();

        // Dispatch the AvailableImageEvent, using the fake uploaded image's path as source
        $availableImage = AvailableImage::factory()->create([
            'path' => $imageUpload->path,
            'id' => $imageUpload->id,
        ]);
        $availableImageEvent = new AvailableImageEvent($availableImage);
        $availableImageListener = new AvailableImageListener;
        $availableImageListener->handle($availableImageEvent);

        // The Listener should have created a new image in the public disk, and have updated
        // the path of the AvailableImage model to point to the new image in the public disk.
        $this->assertDatabaseHas('available_images', [
            'id' => $availableImage->id,
            'path' => $availableImage->path,
        ]);
    }

    public function test_availableimagelistener_removes_the_uploaded_image_from_the_private_disk(): void
    {
        // Create a fake image upload
        $imageUpload = ImageUpload::factory()->create();

        // Dispatch the AvailableImageEvent, using the fake uploaded image's path as source
        $availableImage = AvailableImage::factory()->create([
            'path' => $imageUpload->path,
            'id' => $imageUpload->id,
        ]);
        $availableImageEvent = new AvailableImageEvent($availableImage);
        $availableImageListener = new AvailableImageListener;
        $availableImageListener->handle($availableImageEvent);

        // The Listener should have created a new image in the public disk, and have updated
        // the path of the AvailableImage model to point to the new image in the public disk.
        $availableImage->refresh();
        $this->assertFileDoesNotExist(Storage::disk('local')->path($imageUpload->path));
    }
}

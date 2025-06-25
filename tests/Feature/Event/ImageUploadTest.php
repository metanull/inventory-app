<?php

namespace Tests\Feature;

use App\Events\AvailableImageEvent;
use App\Events\ImageUploadEvent;
use App\Listeners\AvailableImageListener;
use App\Listeners\ImageUploadListener;
use App\Models\AvailableImage;
use App\Models\ImageUpload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageUploadTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        Storage::fake('local');
        Event::fake();
    }

    public function test_imageuploadlistener_listener_is_registered_for_imageuploadevent_event(): void
    {
        Event::assertListening(
            expectedEvent: ImageUploadEvent::class,
            expectedListener: ImageUploadListener::class,
        );
    }

    public function test_store_dispatches_imageuploadevent_event(): void
    {
        $this->postJson(route('image-upload.store'), [
            'file' => UploadedFile::fake()->image('test.jpg'),
        ]);
        Event::assertDispatched(
            ImageUploadEvent::class,
            function (ImageUploadEvent $event) {
                return $event->imageUpload instanceof ImageUpload;
            }
        );
    }

    public function test_imageuploadlistener_listener_dispatches_availableimageevent_event(): void
    {
        $imageUpload = ImageUpload::factory()->create();
        $imageUploadEvent = new ImageUploadEvent($imageUpload);
        $imageUploadListener = new ImageUploadListener;
        $imageUploadListener->handle($imageUploadEvent);
        Event::assertDispatched(
            AvailableImageEvent::class,
            function (AvailableImageEvent $event) {
                return $event->availableImage instanceof AvailableImage;
            }
        );
    }

}

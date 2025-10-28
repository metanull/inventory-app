<?php

declare(strict_types=1);

namespace Tests\Feature\Web\ImageUpload;

use App\Events\ImageUploadEvent;
use App\Models\ImageUpload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class StoreTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
        Storage::fake(config('localstorage.uploads.images.disk'));
    }

    public function test_store_uploads_image_successfully(): void
    {
        Event::fake([ImageUploadEvent::class]);

        $file = UploadedFile::fake()->image('test-image.jpg', 800, 600);

        $response = $this->post(route('images.store'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('images.upload'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('image_uploads', [
            'name' => 'test-image.jpg',
            'extension' => 'jpg',
        ]);

        Event::assertDispatched(ImageUploadEvent::class);
    }

    public function test_store_validates_file_is_required(): void
    {
        $response = $this->post(route('images.store'), [
            'file' => null,
        ]);

        $response->assertSessionHasErrors(['file']);
    }

    public function test_store_validates_file_is_image(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->post(route('images.store'), [
            'file' => $file,
        ]);

        $response->assertSessionHasErrors(['file']);
    }

    public function test_store_validates_file_size(): void
    {
        $maxSize = config('localstorage.uploads.images.max_size', 20480);
        $file = UploadedFile::fake()->image('huge-image.jpg')->size($maxSize + 1);

        $response = $this->post(route('images.store'), [
            'file' => $file,
        ]);

        $response->assertSessionHasErrors(['file']);
    }

    public function test_store_persists_image_metadata(): void
    {
        Event::fake([ImageUploadEvent::class]);

        $file = UploadedFile::fake()->image('metadata-test.png', 1024, 768);

        $response = $this->post(route('images.store'), [
            'file' => $file,
        ]);

        $response->assertRedirect();

        $upload = ImageUpload::latest()->first();
        $this->assertNotNull($upload);
        $this->assertEquals('metadata-test.png', $upload->name);
        $this->assertEquals('png', $upload->extension);
        $this->assertNotNull($upload->path);
        $this->assertNotNull($upload->mime_type);
        $this->assertGreaterThan(0, $upload->size);
    }

    public function test_store_triggers_image_upload_event(): void
    {
        Event::fake([ImageUploadEvent::class]);

        $file = UploadedFile::fake()->image('event-test.jpg');

        $this->post(route('images.store'), [
            'file' => $file,
        ]);

        Event::assertDispatched(ImageUploadEvent::class, function ($event) {
            return $event->imageUpload instanceof ImageUpload;
        });
    }
}

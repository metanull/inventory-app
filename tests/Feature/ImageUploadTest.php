<?php

namespace Tests\Feature;

use App\Models\ImageUpload;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImageUploadTest extends TestCase
{
    public function test_index_requires_authentication(): void
    {
        $response_anonymous = $this->getJson(route('image-upload.index'));
        $response_anonymous->assertUnauthorized();

        $user = User::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->getJson(route('image-upload.index'));
        $response_authenticated->assertOk();
    }

    public function test_show_requires_authentication(): void
    {
        $imageUpload = ImageUpload::factory()->create();

        $response_anonymous = $this->getJson(route('image-upload.show', $imageUpload->id));
        $response_anonymous->assertUnauthorized();

        $user = User::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->getJson(route('image-upload.show', $imageUpload->id));
        $response_authenticated->assertOk();
    }

    public function test_store_requires_authentication(): void
    {
        // Fake the storage disk
        Storage::fake(config('localstorage.uploads.images.disk'));
        $file = UploadedFile::fake()->image('test-image.jpg', 640, 480);

        $response_anonymous = $this->postJson(route('image-upload.store'), [
            'file' => $file,
        ]);
        $response_anonymous->assertUnauthorized();

        $user = User::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->postJson(route('image-upload.store'), [
                'file' => $file,
            ]);
        $response_authenticated->assertCreated();
    }

    public function test_destroy_requires_authentication(): void
    {
        $imageUpload = ImageUpload::factory()->create();

        $response_anonymous = $this->deleteJson(route('image-upload.destroy', $imageUpload->id));
        $response_anonymous->assertUnauthorized();

        $user = User::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->deleteJson(route('image-upload.destroy', $imageUpload->id));
        $response_authenticated->assertNoContent();
    }
}

<?php

namespace Tests\Feature;

use App\Models\ImageUpload;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Tests\TestCase;

class ImageUploadTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_image_upload_factory(): void
    {
        $imageUpload = ImageUpload::factory()->create();

        $this->assertDatabaseHas('image_uploads', [
            'id' => $imageUpload->id,
            'path' => $imageUpload->path,
            'name' => $imageUpload->name,
            'extension' => $imageUpload->extension,
            'mime_type' => $imageUpload->mime_type,
            'size' => $imageUpload->size,
        ]);
    }

    public function test_api_authentication_index_forbids_anonymous_access(): void
    {
        $response = $this->getJson(route('image-upload.index'));
        $response->assertUnauthorized();
    }

    public function test_api_authentication_index_allows_authenticated_users(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson(route('image-upload.index'));
        $response->assertOk();
    }

    public function test_api_authentication_show_forbids_anonymous_access(): void
    {
        $imageUpload = ImageUpload::factory()->create();
        $response = $this->getJson(route('image-upload.show', $imageUpload->id));
        $response->assertUnauthorized();
    }

    public function test_api_authentication_show_allows_authenticated_users(): void
    {
        $user = User::factory()->create();
        $imageUpload = ImageUpload::factory()->create();

        $this->actingAs($user);
        $response = $this->getJson(route('image-upload.show', $imageUpload->id));
        $response->assertOk();
    }

    public function test_api_authentication_store_forbids_anonymous_access(): void
    {
        $response = $this->postJson(route('image-upload.store'), [
            'file' => UploadedFile::fake()->image('test.jpg'),
        ]);
        $response->assertUnauthorized();
    }

    public function test_api_authentication_store_allows_authenticated_users(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Storage::fake('local');
        Queue::fake();

        $response = $this->postJson(route('image-upload.store'), [
            'file' => UploadedFile::fake()->image('test.jpg'),
        ]);
        $response->assertCreated();
    }

    public function test_api_route_update_is_not_found(): void
    {
        $this->expectException(RouteNotFoundException::class);

        Storage::fake('local');
        Queue::fake();

        $response = $this->putJson(route('image-upload.update', 'non-existent-id'), [
            'file' => UploadedFile::fake()->image('updated.jpg'),
        ]);
    }
    
    public function test_api_authentication_destroy_forbids_anonymous_access(): void
    {
        $imageUpload = ImageUpload::factory()->create();
        $response = $this->deleteJson(route('image-upload.destroy', $imageUpload->id));
        $response->assertUnauthorized();
    }

    public function test_api_authentication_destroy_allows_authenticated_users(): void
    {
        $user = User::factory()->create();
        $imageUpload = ImageUpload::factory()->create();

        $this->actingAs($user);
        $response = $this->deleteJson(route('image-upload.destroy', $imageUpload->id));
        $response->assertNoContent();
    }

    public function test_api_response_show_returns_not_found_when_not_found(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson(route('image-upload.show', 'non-existent-id'));
        $response->assertNotFound();
    }
}

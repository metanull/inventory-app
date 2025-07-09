<?php

namespace Tests\Feature\Api\ImageUpload;

use App\Models\ImageUpload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Event::fake();
        Http::fake();
    }

    public function test_index_forbids_anonymous_access(): void
    {
        $response = $this->getJson(route('image-upload.index'));
        $response->assertUnauthorized();
    }

    public function test_show_forbids_anonymous_access(): void
    {
        $imageUpload = ImageUpload::factory()->create();
        $response = $this->getJson(route('image-upload.show', $imageUpload->id));
        $response->assertUnauthorized();
    }

    public function test_store_forbids_anonymous_access(): void
    {
        $response = $this->postJson(route('image-upload.store'), [
            'file' => UploadedFile::fake()->image('test.jpg'),
        ]);
        $response->assertUnauthorized();
    }

    public function test_update_route_is_not_found(): void
    {
        $this->expectException(RouteNotFoundException::class);

        $this->putJson(route('image-upload.update', 'non-existent-id'), [
            'file' => UploadedFile::fake()->image('updated.jpg'),
        ]);
    }

    public function test_update_method_is_not_allowed(): void
    {
        $response = $this->putJson('/api/image-upload/non-existent-id', [
            'file' => UploadedFile::fake()->image('updated.jpg'),
        ]);

        $response->assertMethodNotAllowed();
    }

    public function test_destroy_forbids_anonymous_access(): void
    {
        $imageUpload = ImageUpload::factory()->create();
        $response = $this->deleteJson(route('image-upload.destroy', $imageUpload->id));
        $response->assertUnauthorized();
    }

    public function test_status_forbids_anonymous_access(): void
    {
        $uploadId = '550e8400-e29b-41d4-a716-446655440000';
        $response = $this->getJson(route('image-upload.status', $uploadId));
        $response->assertUnauthorized();
    }
}

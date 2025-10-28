<?php

namespace Tests\Feature\Api\ImageUpload;

use App\Enums\Permission;
use App\Models\AvailableImage;
use App\Models\ImageUpload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class StatusTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWith([Permission::VIEW_DATA->value]);
        $this->actingAs($this->user);

        Storage::fake('local');
        Storage::fake('public');
        Event::fake();
        Http::fake();
    }

    public function test_status_returns_processing_for_existing_image_upload(): void
    {
        $imageUpload = ImageUpload::factory()->create();

        $response = $this->getJson(route('image-upload.status', $imageUpload->id));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'status',
                'available_image',
            ],
        ]);
        $response->assertJsonPath('data.status', 'processing');
        $response->assertJsonPath('data.available_image', null);
    }

    public function test_status_returns_processed_for_available_image(): void
    {
        $uploadId = '550e8400-e29b-41d4-a716-446655440000';
        $availableImage = AvailableImage::factory()->create(['id' => $uploadId]);

        $response = $this->getJson(route('image-upload.status', $uploadId));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'status',
                'available_image' => [
                    'id',
                    'path',
                    'comment',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
        $response->assertJsonPath('data.status', 'processed');
        $response->assertJsonPath('data.available_image.id', $uploadId);
        $response->assertJsonPath('data.available_image.path', $availableImage->path);
    }

    public function test_status_returns_not_found_for_nonexistent_resource(): void
    {
        $nonExistentId = '550e8400-e29b-41d4-a716-446655440000';

        $response = $this->getJson(route('image-upload.status', $nonExistentId));

        $response->assertNotFound();
        $response->assertJsonStructure([
            'status',
            'available_image',
        ]);
        $response->assertJsonPath('status', 'not_found');
        $response->assertJsonPath('available_image', null);
    }

    public function test_status_with_uuid_validation(): void
    {
        $invalidId = 'invalid-uuid';

        $response = $this->getJson(route('image-upload.status', $invalidId));

        // Since we're not explicitly validating UUID format in the controller,
        // it should return not_found for any non-existent ID
        $response->assertNotFound();
        $response->assertJsonPath('status', 'not_found');
    }

    public function test_status_endpoint_accessible_by_authenticated_users(): void
    {
        $imageUpload = ImageUpload::factory()->create();

        $response = $this->getJson(route('image-upload.status', $imageUpload->id));

        $response->assertOk();
        $response->assertJsonPath('data.status', 'processing');
    }
}

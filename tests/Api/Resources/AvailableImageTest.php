<?php

namespace Tests\Api\Resources;

use App\Models\AvailableImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Api\Traits\AuthenticatesApiRequests;
use Tests\Api\Traits\TestsApiImageViewing;
use Tests\TestCase;

class AvailableImageTest extends TestCase
{
    use AuthenticatesApiRequests;
    use RefreshDatabase;
    use TestsApiImageViewing;

    protected function getResourceName(): string
    {
        return 'available-image';
    }

    protected function getModelClass(): string
    {
        return AvailableImage::class;
    }

    public function test_available_image_resource_includes_metadata_fields(): void
    {
        $this->setUpImageStorage();

        $availableImage = AvailableImage::factory()->create([
            'original_name' => 'uploaded-photo.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 54321,
            'comment' => 'A test comment',
        ]);

        $response = $this->getJson(route('available-image.show', $availableImage));

        $response->assertOk()
            ->assertJsonFragment([
                'id' => $availableImage->id,
                'original_name' => 'uploaded-photo.jpg',
                'mime_type' => 'image/jpeg',
                'size' => 54321,
                'comment' => 'A test comment',
            ]);
    }

    public function test_update_request_prohibits_system_managed_metadata_fields(): void
    {
        $this->setUpImageStorage();

        $availableImage = AvailableImage::factory()->create();

        $response = $this->putJson(route('available-image.update', $availableImage), [
            'original_name' => 'hacked.jpg',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['original_name']);
    }

    public function test_update_request_prohibits_mime_type_field(): void
    {
        $this->setUpImageStorage();

        $availableImage = AvailableImage::factory()->create();

        $response = $this->putJson(route('available-image.update', $availableImage), [
            'mime_type' => 'image/png',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['mime_type']);
    }

    public function test_update_request_prohibits_size_field(): void
    {
        $this->setUpImageStorage();

        $availableImage = AvailableImage::factory()->create();

        $response = $this->putJson(route('available-image.update', $availableImage), [
            'size' => 999,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['size']);
    }
}

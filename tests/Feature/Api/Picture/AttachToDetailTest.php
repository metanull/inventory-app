<?php

namespace Tests\Feature\Api\Picture;

use App\Models\AvailableImage;
use App\Models\Detail;
use App\Models\Picture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttachToDetailTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_attach_available_image_to_detail(): void
    {
        // Create a detail
        $detail = Detail::factory()->create();

        // Create an available image
        $availableImage = AvailableImage::factory()->create();

        // Mock the storage operations
        Storage::fake('public');
        Storage::disk('public')->put($availableImage->path, 'fake-image-content');

        $response = $this->postJson(route('picture.attachToDetail', $detail), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Test Detail Picture',
            'backward_compatibility' => null,
            'copyright_text' => 'Test Detail Copyright',
            'copyright_url' => 'https://example.com/detail',
        ]);

        $response->assertCreated();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'internal_name',
                'backward_compatibility',
                'copyright_text',
                'copyright_url',
                'path',
                'upload_name',
                'upload_extension',
                'upload_mime_type',
                'upload_size',
                'pictureable_type',
                'pictureable_id',
                'created_at',
                'updated_at',
            ],
        ]);

        $response->assertJsonPath('data.internal_name', 'Test Detail Picture');
        $response->assertJsonPath('data.copyright_text', 'Test Detail Copyright');
        $response->assertJsonPath('data.copyright_url', 'https://example.com/detail');
        $response->assertJsonPath('data.pictureable_type', 'App\\Models\\Detail');
        $response->assertJsonPath('data.pictureable_id', $detail->id);

        // Verify the picture was created in the database
        $this->assertDatabaseHas('pictures', [
            'internal_name' => 'Test Detail Picture',
            'copyright_text' => 'Test Detail Copyright',
            'copyright_url' => 'https://example.com/detail',
            'pictureable_type' => 'App\\Models\\Detail',
            'pictureable_id' => $detail->id,
        ]);

        // Verify the available image was deleted
        $this->assertDatabaseMissing('available_images', [
            'id' => $availableImage->id,
        ]);

        // Verify the detail has the picture
        $this->assertEquals(1, $detail->pictures()->count());
    }

    public function test_requires_authentication(): void
    {
        $this->withoutAuthentication();
        $detail = Detail::factory()->create();

        $response = $this->postJson(route('picture.attachToDetail', $detail), [
            'available_image_id' => fake()->uuid(),
            'internal_name' => 'Test Picture',
        ]);

        $response->assertUnauthorized();
    }

    public function test_requires_valid_available_image_id(): void
    {
        $detail = Detail::factory()->create();

        $response = $this->postJson(route('picture.attachToDetail', $detail), [
            'available_image_id' => fake()->uuid(),
            'internal_name' => 'Test Picture',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['available_image_id']);
    }

    public function test_requires_internal_name(): void
    {
        $detail = Detail::factory()->create();
        $availableImage = AvailableImage::factory()->create();

        $response = $this->postJson(route('picture.attachToDetail', $detail), [
            'available_image_id' => $availableImage->id,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_validates_copyright_url_format(): void
    {
        $detail = Detail::factory()->create();
        $availableImage = AvailableImage::factory()->create();

        $response = $this->postJson(route('picture.attachToDetail', $detail), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Test Picture',
            'copyright_url' => 'not-a-valid-url',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['copyright_url']);
    }

    public function test_returns_404_when_image_file_not_found(): void
    {
        $detail = Detail::factory()->create();
        $availableImage = AvailableImage::factory()->create();

        // Don't create the actual file, so it won't be found
        Storage::fake('public');

        $response = $this->postJson(route('picture.attachToDetail', $detail), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Test Picture',
        ]);

        $response->assertNotFound();
        $response->assertJson(['error' => 'Image file not found']);
    }

    public function test_handles_optional_fields(): void
    {
        $detail = Detail::factory()->create();
        $availableImage = AvailableImage::factory()->create();

        Storage::fake('public');
        Storage::disk('public')->put($availableImage->path, 'fake-image-content');

        $response = $this->postJson(route('picture.attachToDetail', $detail), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Test Picture',
            'backward_compatibility' => 'legacy-detail-456',
            'copyright_text' => null,
            'copyright_url' => null,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.backward_compatibility', 'legacy-detail-456');
        $response->assertJsonPath('data.copyright_text', null);
        $response->assertJsonPath('data.copyright_url', null);
    }

    public function test_creates_picture_with_correct_file_information(): void
    {
        $detail = Detail::factory()->create();
        $availableImage = AvailableImage::factory()->create([
            'path' => 'images/detail-image.png',
        ]);

        Storage::fake('public');
        Storage::disk('public')->put($availableImage->path, 'fake-image-content');

        $response = $this->postJson(route('picture.attachToDetail', $detail), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Test Picture',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.upload_name', 'detail-image.png');
        $response->assertJsonPath('data.upload_extension', 'png');
        $response->assertJsonPath('data.path', 'pictures/detail-image.png');
    }

    public function test_allows_multiple_pictures_per_detail(): void
    {
        $detail = Detail::factory()->create();
        $availableImage1 = AvailableImage::factory()->create();
        $availableImage2 = AvailableImage::factory()->create();

        // Mock the storage operations
        Storage::fake('public');
        Storage::disk('public')->put($availableImage1->path, 'fake-image-content-1');
        Storage::disk('public')->put($availableImage2->path, 'fake-image-content-2');

        // First attachment
        $response1 = $this->postJson(route('picture.attachToDetail', $detail), [
            'available_image_id' => $availableImage1->id,
            'internal_name' => 'Picture 1',
        ]);
        $response1->assertCreated();

        // Second attachment to same detail should succeed
        $response2 = $this->postJson(route('picture.attachToDetail', $detail), [
            'available_image_id' => $availableImage2->id,
            'internal_name' => 'Picture 2',
        ]);
        $response2->assertCreated();

        // Verify both pictures are attached to the detail
        $this->assertEquals(2, $detail->pictures()->count());
    }

    public function test_prevents_attaching_same_available_image_twice(): void
    {
        $detail = Detail::factory()->create();
        $availableImage = AvailableImage::factory()->create();

        // Mock the storage operations
        Storage::fake('public');
        Storage::disk('public')->put($availableImage->path, 'fake-image-content');

        // First attachment should succeed and delete the available image
        $response1 = $this->postJson(route('picture.attachToDetail', $detail), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Picture 1',
        ]);
        $response1->assertCreated();

        // Verify the available image was deleted after first attachment
        $this->assertDatabaseMissing('available_images', [
            'id' => $availableImage->id,
        ]);

        // Second attachment with same available_image_id should fail because it no longer exists
        $response2 = $this->postJson(route('picture.attachToDetail', $detail), [
            'available_image_id' => $availableImage->id, // This ID no longer exists after first attachment
            'internal_name' => 'Picture 2',
        ]);
        $response2->assertUnprocessable();
        $response2->assertJsonValidationErrors(['available_image_id']);
    }

    private function withoutAuthentication(): void
    {
        $this->user = null;
        $this->app['auth']->forgetGuards();
    }
}

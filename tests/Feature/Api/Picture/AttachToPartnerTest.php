<?php

namespace Tests\Feature\Api\Picture;

use App\Models\AvailableImage;
use App\Models\Partner;
use App\Models\Picture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttachToPartnerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_attach_available_image_to_partner(): void
    {
        // Create a partner
        $partner = Partner::factory()->create();

        // Create an available image
        $availableImage = AvailableImage::factory()->create();

        // Mock the storage operations
        Storage::fake('public');
        Storage::disk('public')->put($availableImage->path, 'fake-image-content');

        $response = $this->postJson(route('picture.attachToPartner', $partner), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Test Partner Picture',
            'backward_compatibility' => null,
            'copyright_text' => 'Test Partner Copyright',
            'copyright_url' => 'https://example.com/partner',
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

        $response->assertJsonPath('data.internal_name', 'Test Partner Picture');
        $response->assertJsonPath('data.copyright_text', 'Test Partner Copyright');
        $response->assertJsonPath('data.copyright_url', 'https://example.com/partner');
        $response->assertJsonPath('data.pictureable_type', 'App\\Models\\Partner');
        $response->assertJsonPath('data.pictureable_id', $partner->id);

        // Verify the picture was created in the database
        $this->assertDatabaseHas('pictures', [
            'internal_name' => 'Test Partner Picture',
            'copyright_text' => 'Test Partner Copyright',
            'copyright_url' => 'https://example.com/partner',
            'pictureable_type' => 'App\\Models\\Partner',
            'pictureable_id' => $partner->id,
        ]);

        // Verify the available image was deleted
        $this->assertDatabaseMissing('available_images', [
            'id' => $availableImage->id,
        ]);

        // Verify the partner has the picture
        $this->assertEquals(1, $partner->pictures()->count());
    }

    public function test_requires_authentication(): void
    {
        $this->withoutAuthentication();
        $partner = Partner::factory()->create();

        $response = $this->postJson(route('picture.attachToPartner', $partner), [
            'available_image_id' => fake()->uuid(),
            'internal_name' => 'Test Picture',
        ]);

        $response->assertUnauthorized();
    }

    public function test_requires_valid_available_image_id(): void
    {
        $partner = Partner::factory()->create();

        $response = $this->postJson(route('picture.attachToPartner', $partner), [
            'available_image_id' => fake()->uuid(),
            'internal_name' => 'Test Picture',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['available_image_id']);
    }

    public function test_requires_internal_name(): void
    {
        $partner = Partner::factory()->create();
        $availableImage = AvailableImage::factory()->create();

        $response = $this->postJson(route('picture.attachToPartner', $partner), [
            'available_image_id' => $availableImage->id,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_validates_copyright_url_format(): void
    {
        $partner = Partner::factory()->create();
        $availableImage = AvailableImage::factory()->create();

        $response = $this->postJson(route('picture.attachToPartner', $partner), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Test Picture',
            'copyright_url' => 'not-a-valid-url',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['copyright_url']);
    }

    public function test_returns_404_when_image_file_not_found(): void
    {
        $partner = Partner::factory()->create();
        $availableImage = AvailableImage::factory()->create();

        // Don't create the actual file, so it won't be found
        Storage::fake('public');

        $response = $this->postJson(route('picture.attachToPartner', $partner), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Test Picture',
        ]);

        $response->assertNotFound();
        $response->assertJson(['error' => 'Image file not found']);
    }

    public function test_handles_optional_fields(): void
    {
        $partner = Partner::factory()->create();
        $availableImage = AvailableImage::factory()->create();

        Storage::fake('public');
        Storage::disk('public')->put($availableImage->path, 'fake-image-content');

        $response = $this->postJson(route('picture.attachToPartner', $partner), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Test Picture',
            'backward_compatibility' => 'legacy-partner-789',
            'copyright_text' => null,
            'copyright_url' => null,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.backward_compatibility', 'legacy-partner-789');
        $response->assertJsonPath('data.copyright_text', null);
        $response->assertJsonPath('data.copyright_url', null);
    }

    public function test_creates_picture_with_correct_file_information(): void
    {
        $partner = Partner::factory()->create();
        $availableImage = AvailableImage::factory()->create([
            'path' => 'images/partner-logo.gif',
        ]);

        Storage::fake('public');
        Storage::disk('public')->put($availableImage->path, 'fake-image-content');

        $response = $this->postJson(route('picture.attachToPartner', $partner), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Test Picture',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.upload_name', 'partner-logo.gif');
        $response->assertJsonPath('data.upload_extension', 'gif');
        $response->assertJsonPath('data.path', 'pictures/partner-logo.gif');
    }

    public function test_allows_multiple_pictures_per_partner(): void
    {
        $partner = Partner::factory()->create();
        $availableImage1 = AvailableImage::factory()->create();
        $availableImage2 = AvailableImage::factory()->create();

        // Mock the storage operations
        Storage::fake('public');
        Storage::disk('public')->put($availableImage1->path, 'fake-image-content-1');
        Storage::disk('public')->put($availableImage2->path, 'fake-image-content-2');

        // First attachment
        $response1 = $this->postJson(route('picture.attachToPartner', $partner), [
            'available_image_id' => $availableImage1->id,
            'internal_name' => 'Picture 1',
        ]);
        $response1->assertCreated();

        // Second attachment to same partner should succeed
        $response2 = $this->postJson(route('picture.attachToPartner', $partner), [
            'available_image_id' => $availableImage2->id,
            'internal_name' => 'Picture 2',
        ]);
        $response2->assertCreated();

        // Verify both pictures are attached to the partner
        $this->assertEquals(2, $partner->pictures()->count());
    }

    public function test_prevents_attaching_same_available_image_twice(): void
    {
        $partner = Partner::factory()->create();
        $availableImage = AvailableImage::factory()->create();

        // Mock the storage operations
        Storage::fake('public');
        Storage::disk('public')->put($availableImage->path, 'fake-image-content');

        // First attachment should succeed and delete the available image
        $response1 = $this->postJson(route('picture.attachToPartner', $partner), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Picture 1',
        ]);
        $response1->assertCreated();

        // Verify the available image was deleted after first attachment
        $this->assertDatabaseMissing('available_images', [
            'id' => $availableImage->id,
        ]);

        // Second attachment with same available_image_id should fail because it no longer exists
        $response2 = $this->postJson(route('picture.attachToPartner', $partner), [
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

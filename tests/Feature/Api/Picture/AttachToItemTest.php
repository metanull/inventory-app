<?php

namespace Tests\Feature\Api\Picture;

use App\Models\AvailableImage;
use App\Models\Item;
use App\Models\Picture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttachToItemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_attach_available_image_to_item(): void
    {
        // Create an item
        $item = Item::factory()->create();

        // Create an available image
        $availableImage = AvailableImage::factory()->create();

        // Mock the storage operations
        Storage::fake('public');
        Storage::disk('public')->put($availableImage->path, 'fake-image-content');

        $response = $this->postJson(route('picture.attachToItem', $item), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Test Picture',
            'backward_compatibility' => null,
            'copyright_text' => 'Test Copyright',
            'copyright_url' => 'https://example.com',
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

        $response->assertJsonPath('data.internal_name', 'Test Picture');
        $response->assertJsonPath('data.copyright_text', 'Test Copyright');
        $response->assertJsonPath('data.copyright_url', 'https://example.com');
        $response->assertJsonPath('data.pictureable_type', 'App\\Models\\Item');
        $response->assertJsonPath('data.pictureable_id', $item->id);

        // Verify the picture was created in the database
        $this->assertDatabaseHas('pictures', [
            'internal_name' => 'Test Picture',
            'copyright_text' => 'Test Copyright',
            'copyright_url' => 'https://example.com',
            'pictureable_type' => 'App\\Models\\Item',
            'pictureable_id' => $item->id,
        ]);

        // Verify the available image was deleted
        $this->assertDatabaseMissing('available_images', [
            'id' => $availableImage->id,
        ]);

        // Verify the item has the picture
        $this->assertEquals(1, $item->pictures()->count());
    }

    public function test_requires_authentication(): void
    {
        $this->withoutAuthentication();
        $item = Item::factory()->create();

        $response = $this->postJson(route('picture.attachToItem', $item), [
            'available_image_id' => fake()->uuid(),
            'internal_name' => 'Test Picture',
        ]);

        $response->assertUnauthorized();
    }

    public function test_requires_valid_available_image_id(): void
    {
        $item = Item::factory()->create();

        $response = $this->postJson(route('picture.attachToItem', $item), [
            'available_image_id' => fake()->uuid(),
            'internal_name' => 'Test Picture',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['available_image_id']);
    }

    public function test_requires_internal_name(): void
    {
        $item = Item::factory()->create();
        $availableImage = AvailableImage::factory()->create();

        $response = $this->postJson(route('picture.attachToItem', $item), [
            'available_image_id' => $availableImage->id,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_validates_copyright_url_format(): void
    {
        $item = Item::factory()->create();
        $availableImage = AvailableImage::factory()->create();

        $response = $this->postJson(route('picture.attachToItem', $item), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Test Picture',
            'copyright_url' => 'not-a-valid-url',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['copyright_url']);
    }

    public function test_returns_404_when_image_file_not_found(): void
    {
        $item = Item::factory()->create();
        $availableImage = AvailableImage::factory()->create();

        // Don't create the actual file, so it won't be found
        Storage::fake('public');

        $response = $this->postJson(route('picture.attachToItem', $item), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Test Picture',
        ]);

        $response->assertNotFound();
        $response->assertJson(['error' => 'Image file not found']);
    }

    public function test_handles_optional_fields(): void
    {
        $item = Item::factory()->create();
        $availableImage = AvailableImage::factory()->create();

        Storage::fake('public');
        Storage::disk('public')->put($availableImage->path, 'fake-image-content');

        $response = $this->postJson(route('picture.attachToItem', $item), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Test Picture',
            'backward_compatibility' => 'legacy-id-123',
            'copyright_text' => null,
            'copyright_url' => null,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.backward_compatibility', 'legacy-id-123');
        $response->assertJsonPath('data.copyright_text', null);
        $response->assertJsonPath('data.copyright_url', null);
    }

    public function test_creates_picture_with_correct_file_information(): void
    {
        $item = Item::factory()->create();
        $availableImage = AvailableImage::factory()->create([
            'path' => 'images/test-image.jpg',
        ]);

        Storage::fake('public');
        Storage::disk('public')->put($availableImage->path, 'fake-image-content');

        $response = $this->postJson(route('picture.attachToItem', $item), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Test Picture',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.upload_name', 'test-image.jpg');
        $response->assertJsonPath('data.upload_extension', 'jpg');
        $response->assertJsonPath('data.path', 'pictures/test-image.jpg');
    }

    public function test_moves_file_from_available_images_to_pictures_directory(): void
    {
        $item = Item::factory()->create();
        $availableImage = AvailableImage::factory()->create([
            'path' => 'images/move-test.png',
        ]);

        Storage::fake('public');
        $fileContent = 'original-image-content-for-move-test';
        Storage::disk('public')->put($availableImage->path, $fileContent);

        // Verify initial state: file exists in available images, not in pictures
        $this->assertTrue(Storage::disk('public')->exists('images/move-test.png'));
        $this->assertFalse(Storage::disk('public')->exists('pictures/move-test.png'));

        $response = $this->postJson(route('picture.attachToItem', $item), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Move Test Picture',
        ]);

        $response->assertCreated();

        // Verify final state: file moved from available images to pictures
        $this->assertFalse(Storage::disk('public')->exists('images/move-test.png'));
        $this->assertTrue(Storage::disk('public')->exists('pictures/move-test.png'));

        // Verify file content is preserved
        $this->assertEquals($fileContent, Storage::disk('public')->get('pictures/move-test.png'));
    }

    public function test_deletes_available_image_record_after_successful_attachment(): void
    {
        $item = Item::factory()->create();
        $availableImage = AvailableImage::factory()->create();

        Storage::fake('public');
        Storage::disk('public')->put($availableImage->path, 'fake-image-content');

        // Verify AvailableImage exists before attachment
        $this->assertDatabaseHas('available_images', [
            'id' => $availableImage->id,
            'path' => $availableImage->path,
        ]);

        $response = $this->postJson(route('picture.attachToItem', $item), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Test Picture',
        ]);

        $response->assertCreated();

        // Verify AvailableImage record is deleted
        $this->assertDatabaseMissing('available_images', [
            'id' => $availableImage->id,
        ]);

        // Verify Picture record is created
        $this->assertDatabaseHas('pictures', [
            'internal_name' => 'Test Picture',
            'pictureable_type' => 'App\\Models\\Item',
            'pictureable_id' => $item->id,
        ]);
    }

    public function test_preserves_file_metadata_during_attachment(): void
    {
        $item = Item::factory()->create();
        $availableImage = AvailableImage::factory()->create([
            'path' => 'images/metadata-test.jpg',
        ]);

        Storage::fake('public');
        $fileContent = 'test-image-content-with-metadata';
        Storage::disk('public')->put($availableImage->path, $fileContent);

        $response = $this->postJson(route('picture.attachToItem', $item), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Metadata Test Picture',
        ]);

        $response->assertCreated();

        // Verify the Picture has correct file metadata
        $picture = Picture::where('internal_name', 'Metadata Test Picture')->first();
        $this->assertNotNull($picture);
        $this->assertEquals('pictures/metadata-test.jpg', $picture->path);
        $this->assertEquals('metadata-test.jpg', $picture->upload_name);
        $this->assertEquals('jpg', $picture->upload_extension);
        $this->assertEquals(strlen($fileContent), $picture->upload_size);

        // Verify the actual file exists with correct content
        $this->assertTrue(Storage::disk('public')->exists($picture->path));
        $this->assertEquals($fileContent, Storage::disk('public')->get($picture->path));
    }

    public function test_file_content_preservation_during_attachment(): void
    {
        // Create an item and available image with specific content
        $item = Item::factory()->create();
        $availableImage = AvailableImage::factory()->create([
            'path' => 'images/preserve-test.jpg',
        ]);

        // Mock storage and create file with unique content
        Storage::fake('public');
        $uniqueContent = 'unique-content-'.uniqid();
        Storage::disk('public')->put($availableImage->path, $uniqueContent);

        // Verify initial state
        $this->assertTrue(Storage::disk('public')->exists('images/preserve-test.jpg'));
        $this->assertFalse(Storage::disk('public')->exists('pictures/preserve-test.jpg'));
        $this->assertEquals($uniqueContent, Storage::disk('public')->get('images/preserve-test.jpg'));

        $response = $this->postJson(route('picture.attachToItem', $item), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Preserve Test Picture',
        ]);

        $response->assertCreated();

        // Verify final state - file moved and content preserved
        $this->assertFalse(Storage::disk('public')->exists('images/preserve-test.jpg'));
        $this->assertTrue(Storage::disk('public')->exists('pictures/preserve-test.jpg'));
        $this->assertEquals($uniqueContent, Storage::disk('public')->get('pictures/preserve-test.jpg'));
    }

    public function test_database_records_transition_correctly_during_attachment(): void
    {
        $item = Item::factory()->create();
        $availableImage = AvailableImage::factory()->create([
            'path' => 'images/db-test.png',
            'comment' => 'Original available image comment',
        ]);

        Storage::fake('public');
        Storage::disk('public')->put($availableImage->path, 'test-content');

        // Verify initial database state
        $this->assertDatabaseHas('available_images', [
            'id' => $availableImage->id,
            'path' => 'images/db-test.png',
            'comment' => 'Original available image comment',
        ]);
        $this->assertDatabaseCount('pictures', 0);
        $this->assertEquals(0, $item->pictures()->count());

        $response = $this->postJson(route('picture.attachToItem', $item), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'DB Test Picture',
            'copyright_text' => 'Test Copyright',
        ]);

        $response->assertCreated();

        // Verify final database state
        $this->assertDatabaseMissing('available_images', [
            'id' => $availableImage->id,
        ]);
        $this->assertDatabaseCount('available_images', 0);
        $this->assertDatabaseCount('pictures', 1);

        $picture = Picture::first();
        $this->assertDatabaseHas('pictures', [
            'id' => $picture->id,
            'internal_name' => 'DB Test Picture',
            'copyright_text' => 'Test Copyright',
            'path' => 'pictures/db-test.png',
            'upload_name' => 'db-test.png',
            'pictureable_type' => get_class($item),
            'pictureable_id' => $item->id,
        ]);

        $this->assertEquals(1, $item->fresh()->pictures()->count());
        $this->assertTrue($item->fresh()->pictures->contains($picture));
    }

    public function test_atomic_transaction_behavior_during_attachment(): void
    {
        $item = Item::factory()->create();
        $availableImage = AvailableImage::factory()->create([
            'path' => 'images/atomic-test.gif',
        ]);

        Storage::fake('public');
        Storage::disk('public')->put($availableImage->path, 'atomic-test-content');

        // Verify initial state
        $initialAvailableCount = AvailableImage::count();
        $initialPictureCount = Picture::count();
        $initialItemPictureCount = $item->pictures()->count();

        $response = $this->postJson(route('picture.attachToItem', $item), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Atomic Test Picture',
        ]);

        $response->assertCreated();

        // Verify atomic changes - all operations completed together
        $this->assertEquals($initialAvailableCount - 1, AvailableImage::count());
        $this->assertEquals($initialPictureCount + 1, Picture::count());
        $this->assertEquals($initialItemPictureCount + 1, $item->fresh()->pictures()->count());

        // Verify no orphaned records or files
        $this->assertFalse(Storage::disk('public')->exists('images/atomic-test.gif'));
        $this->assertTrue(Storage::disk('public')->exists('pictures/atomic-test.gif'));
    }

    public function test_attachment_preserves_file_metadata(): void
    {
        $item = Item::factory()->create();
        $availableImage = AvailableImage::factory()->create([
            'path' => 'images/metadata-test.webp',
        ]);

        Storage::fake('public');
        $testContent = 'metadata-preservation-test-content';
        Storage::disk('public')->put($availableImage->path, $testContent);

        $response = $this->postJson(route('picture.attachToItem', $item), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Metadata Test Picture',
        ]);

        $response->assertCreated();

        $picture = Picture::first();

        // Verify file metadata is preserved in Picture record
        $this->assertEquals('pictures/metadata-test.webp', $picture->path);
        $this->assertEquals('metadata-test.webp', $picture->upload_name);
        $this->assertEquals('webp', $picture->upload_extension);
        $this->assertEquals(strlen($testContent), $picture->upload_size);

        // Verify actual file characteristics
        $this->assertEquals($testContent, Storage::disk('public')->get($picture->path));
        $this->assertEquals(strlen($testContent), Storage::disk('public')->size($picture->path));
    }

    public function test_allows_multiple_pictures_per_item(): void
    {
        $item = Item::factory()->create();
        $availableImage1 = AvailableImage::factory()->create();
        $availableImage2 = AvailableImage::factory()->create();

        // Mock the storage operations
        Storage::fake('public');
        Storage::disk('public')->put($availableImage1->path, 'fake-image-content-1');
        Storage::disk('public')->put($availableImage2->path, 'fake-image-content-2');

        // First attachment
        $response1 = $this->postJson(route('picture.attachToItem', $item), [
            'available_image_id' => $availableImage1->id,
            'internal_name' => 'Picture 1',
        ]);
        $response1->assertCreated();

        // Second attachment to same item should succeed
        $response2 = $this->postJson(route('picture.attachToItem', $item), [
            'available_image_id' => $availableImage2->id,
            'internal_name' => 'Picture 2',
        ]);
        $response2->assertCreated();

        // Verify both pictures are attached to the item
        $this->assertEquals(2, $item->pictures()->count());
    }

    public function test_prevents_attaching_same_available_image_twice(): void
    {
        $item = Item::factory()->create();
        $availableImage = AvailableImage::factory()->create();

        // Mock the storage operations
        Storage::fake('public');
        Storage::disk('public')->put($availableImage->path, 'fake-image-content');

        // First attachment should succeed and delete the available image
        $response1 = $this->postJson(route('picture.attachToItem', $item), [
            'available_image_id' => $availableImage->id,
            'internal_name' => 'Picture 1',
        ]);
        $response1->assertCreated();

        // Verify the available image was deleted after first attachment
        $this->assertDatabaseMissing('available_images', [
            'id' => $availableImage->id,
        ]);

        // Second attachment with same available_image_id should fail because it no longer exists
        $response2 = $this->postJson(route('picture.attachToItem', $item), [
            'available_image_id' => $availableImage->id, // This ID no longer exists
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

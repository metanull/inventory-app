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

class DetachFromItemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_detach_picture_from_item(): void
    {
        // Create an item with a picture
        $item = Item::factory()->create();
        $picture = Picture::factory()->forItem()->create([
            'pictureable_id' => $item->id,
            'pictureable_type' => get_class($item),
        ]);

        // Mock the storage operations
        Storage::fake('public');
        Storage::disk('public')->put($picture->path, 'fake-image-content');

        $response = $this->deleteJson(route('picture.detachFromItem', [$item, $picture]), [
            'comment' => 'Test detach comment',
        ]);

        $response->assertOk();
        $response->assertJsonStructure([
            'message',
            'available_image' => [
                'id',
                'path',
                'comment',
                'created_at',
                'updated_at',
            ],
        ]);

        $response->assertJsonPath('message', 'Picture detached successfully');
        $response->assertJsonPath('available_image.comment', 'Test detach comment');

        // Verify the picture was deleted from database
        $this->assertDatabaseMissing('pictures', [
            'id' => $picture->id,
        ]);

        // Verify an available image was created
        $this->assertDatabaseHas('available_images', [
            'path' => 'images/'.basename($picture->path),
            'comment' => 'Test detach comment',
        ]);

        // Verify the item no longer has the picture
        $this->assertEquals(0, $item->pictures()->count());
    }

    public function test_can_detach_picture_with_default_comment(): void
    {
        // Create an item with a picture
        $item = Item::factory()->create();
        $picture = Picture::factory()->forItem()->create([
            'pictureable_id' => $item->id,
            'pictureable_type' => get_class($item),
        ]);

        // Mock the storage operations
        Storage::fake('public');
        Storage::disk('public')->put($picture->path, 'fake-image-content');

        $response = $this->deleteJson(route('picture.detachFromItem', [$item, $picture]));

        $response->assertOk();
        $response->assertJsonPath('message', 'Picture detached successfully');
        $response->assertJsonPath('available_image.comment', "Detached from App\\Models\\Item ({$item->id})");

        // Verify an available image was created with default comment
        $this->assertDatabaseHas('available_images', [
            'path' => 'images/'.basename($picture->path),
            'comment' => "Detached from App\\Models\\Item ({$item->id})",
        ]);
    }

    public function test_requires_authentication(): void
    {
        $this->withoutAuthentication();
        $item = Item::factory()->create();
        $picture = Picture::factory()->forItem()->create([
            'pictureable_id' => $item->id,
            'pictureable_type' => get_class($item),
        ]);

        $response = $this->deleteJson(route('picture.detachFromItem', [$item, $picture]));

        $response->assertUnauthorized();
    }

    public function test_validates_picture_belongs_to_item(): void
    {
        $item = Item::factory()->create();
        $otherItem = Item::factory()->create();
        $picture = Picture::factory()->forItem()->create([
            'pictureable_id' => $otherItem->id,
            'pictureable_type' => get_class($otherItem),
        ]);

        $response = $this->deleteJson(route('picture.detachFromItem', [$item, $picture]));

        $response->assertUnprocessable();
        $response->assertJson(['error' => 'Picture does not belong to this model']);
    }

    public function test_validates_comment_length(): void
    {
        $item = Item::factory()->create();
        $picture = Picture::factory()->forItem()->create([
            'pictureable_id' => $item->id,
            'pictureable_type' => get_class($item),
        ]);

        $response = $this->deleteJson(route('picture.detachFromItem', [$item, $picture]), [
            'comment' => str_repeat('a', 1001), // Too long
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['comment']);
    }

    public function test_returns_404_when_picture_file_not_found(): void
    {
        $item = Item::factory()->create();
        $picture = Picture::factory()->forItem()->create([
            'pictureable_id' => $item->id,
            'pictureable_type' => get_class($item),
        ]);

        // Don't create the actual file, so it won't be found
        Storage::fake('public');

        $response = $this->deleteJson(route('picture.detachFromItem', [$item, $picture]));

        $response->assertNotFound();
        $response->assertJson(['error' => 'Picture file not found']);
    }

    public function test_returns_404_for_non_existent_item(): void
    {
        $picture = Picture::factory()->forItem()->create();

        $response = $this->deleteJson(route('picture.detachFromItem', [fake()->uuid(), $picture]));

        $response->assertNotFound();
    }

    public function test_returns_404_for_non_existent_picture(): void
    {
        $item = Item::factory()->create();

        $response = $this->deleteJson(route('picture.detachFromItem', [$item, fake()->uuid()]));

        $response->assertNotFound();
    }

    public function test_moves_file_from_pictures_to_available_images(): void
    {
        $item = Item::factory()->create();
        $picture = Picture::factory()->forItem()->create([
            'pictureable_id' => $item->id,
            'pictureable_type' => get_class($item),
            'path' => 'pictures/test-image.jpg',
        ]);

        Storage::fake('public');
        Storage::disk('public')->put($picture->path, 'fake-image-content');

        $response = $this->deleteJson(route('picture.detachFromItem', [$item, $picture]));

        $response->assertOk();
        $response->assertJsonPath('available_image.path', 'images/test-image.jpg');

        // Verify the file was moved
        $this->assertTrue(Storage::disk('public')->exists('images/test-image.jpg'));
        $this->assertFalse(Storage::disk('public')->exists('pictures/test-image.jpg'));
    }

    public function test_preserves_file_content_during_detachment(): void
    {
        $item = Item::factory()->create();
        $picture = Picture::factory()->forItem()->create([
            'pictureable_id' => $item->id,
            'pictureable_type' => get_class($item),
            'path' => 'pictures/content-test.png',
        ]);

        Storage::fake('public');
        $originalContent = 'preserved-image-content-during-detach';
        Storage::disk('public')->put($picture->path, $originalContent);

        // Verify initial state
        $this->assertTrue(Storage::disk('public')->exists('pictures/content-test.png'));
        $this->assertFalse(Storage::disk('public')->exists('images/content-test.png'));
        $this->assertEquals($originalContent, Storage::disk('public')->get('pictures/content-test.png'));

        $response = $this->deleteJson(route('picture.detachFromItem', [$item, $picture]));

        $response->assertOk();

        // Verify final state and content preservation
        $this->assertFalse(Storage::disk('public')->exists('pictures/content-test.png'));
        $this->assertTrue(Storage::disk('public')->exists('images/content-test.png'));
        $this->assertEquals($originalContent, Storage::disk('public')->get('images/content-test.png'));
    }

    public function test_creates_available_image_record_after_successful_detachment(): void
    {
        $item = Item::factory()->create();
        $picture = Picture::factory()->forItem()->create([
            'pictureable_id' => $item->id,
            'pictureable_type' => get_class($item),
            'path' => 'pictures/record-test.gif',
            'internal_name' => 'Record Test Picture',
        ]);

        Storage::fake('public');
        Storage::disk('public')->put($picture->path, 'fake-image-content');

        // Verify Picture exists before detachment
        $this->assertDatabaseHas('pictures', [
            'id' => $picture->id,
            'internal_name' => 'Record Test Picture',
        ]);

        $response = $this->deleteJson(route('picture.detachFromItem', [$item, $picture]), [
            'comment' => 'Detached for testing record creation',
        ]);

        $response->assertOk();

        // Verify Picture record is deleted
        $this->assertDatabaseMissing('pictures', [
            'id' => $picture->id,
        ]);

        // Verify AvailableImage record is created
        $this->assertDatabaseHas('available_images', [
            'path' => 'images/record-test.gif',
            'comment' => 'Detached for testing record creation',
        ]);

        // Verify the new AvailableImage has correct data
        $availableImage = AvailableImage::where('path', 'images/record-test.gif')->first();
        $this->assertNotNull($availableImage);
        $this->assertEquals('Detached for testing record creation', $availableImage->comment);
    }

    public function test_removes_picture_from_item_relationship(): void
    {
        $item = Item::factory()->create();
        $picture1 = Picture::factory()->forItem()->create([
            'pictureable_id' => $item->id,
            'pictureable_type' => get_class($item),
        ]);
        $picture2 = Picture::factory()->forItem()->create([
            'pictureable_id' => $item->id,
            'pictureable_type' => get_class($item),
        ]);

        Storage::fake('public');
        Storage::disk('public')->put($picture1->path, 'fake-content-1');
        Storage::disk('public')->put($picture2->path, 'fake-content-2');

        // Verify item has 2 pictures initially
        $this->assertEquals(2, $item->fresh()->pictures()->count());

        $response = $this->deleteJson(route('picture.detachFromItem', [$item, $picture1]));

        $response->assertOk();

        // Verify item now has 1 picture (picture2 remains)
        $this->assertEquals(1, $item->fresh()->pictures()->count());
        $this->assertTrue($item->fresh()->pictures->contains($picture2));
        $this->assertFalse($item->fresh()->pictures->contains($picture1));
    }

    public function test_handles_different_file_extensions_correctly(): void
    {
        $item = Item::factory()->create();

        $extensions = ['jpg', 'png', 'gif', 'webp'];

        foreach ($extensions as $ext) {
            $picture = Picture::factory()->forItem()->create([
                'pictureable_id' => $item->id,
                'pictureable_type' => get_class($item),
                'path' => "pictures/test-file.{$ext}",
            ]);

            Storage::fake('public');
            Storage::disk('public')->put($picture->path, "fake-{$ext}-content");

            $response = $this->deleteJson(route('picture.detachFromItem', [$item, $picture]));

            $response->assertOk();
            $response->assertJsonPath('available_image.path', "images/test-file.{$ext}");

            // Verify file moved correctly
            $this->assertTrue(Storage::disk('public')->exists("images/test-file.{$ext}"));
            $this->assertFalse(Storage::disk('public')->exists("pictures/test-file.{$ext}"));
            $this->assertEquals("fake-{$ext}-content", Storage::disk('public')->get("images/test-file.{$ext}"));

            // Clean up for next iteration
            Storage::disk('public')->delete("images/test-file.{$ext}");
        }
    }

    private function withoutAuthentication(): void
    {
        $this->user = null;
        $this->app['auth']->forgetGuards();
    }
}

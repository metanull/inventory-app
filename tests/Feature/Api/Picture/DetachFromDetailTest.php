<?php

namespace Tests\Feature\Api\Picture;

use App\Models\Detail;
use App\Models\Picture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DetachFromDetailTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_detach_picture_from_detail(): void
    {
        // Create a detail with a picture
        $detail = Detail::factory()->create();
        $picture = Picture::factory()->forDetail()->create([
            'pictureable_id' => $detail->id,
            'pictureable_type' => get_class($detail),
        ]);

        // Mock the storage operations
        Storage::fake('public');
        Storage::disk('public')->put($picture->path, 'fake-image-content');

        $response = $this->deleteJson(route('picture.detachFromDetail', [$detail, $picture]), [
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

        // Verify the detail no longer has the picture
        $this->assertEquals(0, $detail->pictures()->count());
    }

    public function test_can_detach_picture_with_default_comment(): void
    {
        // Create a detail with a picture
        $detail = Detail::factory()->create();
        $picture = Picture::factory()->forDetail()->create([
            'pictureable_id' => $detail->id,
            'pictureable_type' => get_class($detail),
        ]);

        // Mock the storage operations
        Storage::fake('public');
        Storage::disk('public')->put($picture->path, 'fake-image-content');

        $response = $this->deleteJson(route('picture.detachFromDetail', [$detail, $picture]));

        $response->assertOk();
        $response->assertJsonPath('message', 'Picture detached successfully');
        $response->assertJsonPath('available_image.comment', "Detached from App\\Models\\Detail ({$detail->id})");

        // Verify an available image was created with default comment
        $this->assertDatabaseHas('available_images', [
            'path' => 'images/'.basename($picture->path),
            'comment' => "Detached from App\\Models\\Detail ({$detail->id})",
        ]);
    }

    public function test_requires_authentication(): void
    {
        $this->withoutAuthentication();
        $detail = Detail::factory()->create();
        $picture = Picture::factory()->forDetail()->create([
            'pictureable_id' => $detail->id,
            'pictureable_type' => get_class($detail),
        ]);

        $response = $this->deleteJson(route('picture.detachFromDetail', [$detail, $picture]));

        $response->assertUnauthorized();
    }

    public function test_validates_picture_belongs_to_detail(): void
    {
        $detail = Detail::factory()->create();
        $otherDetail = Detail::factory()->create();
        $picture = Picture::factory()->forDetail()->create([
            'pictureable_id' => $otherDetail->id,
            'pictureable_type' => get_class($otherDetail),
        ]);

        $response = $this->deleteJson(route('picture.detachFromDetail', [$detail, $picture]));

        $response->assertUnprocessable();
        $response->assertJson(['error' => 'Picture does not belong to this model']);
    }

    public function test_validates_comment_length(): void
    {
        $detail = Detail::factory()->create();
        $picture = Picture::factory()->forDetail()->create([
            'pictureable_id' => $detail->id,
            'pictureable_type' => get_class($detail),
        ]);

        $response = $this->deleteJson(route('picture.detachFromDetail', [$detail, $picture]), [
            'comment' => str_repeat('a', 1001), // Too long
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['comment']);
    }

    public function test_returns_404_when_picture_file_not_found(): void
    {
        $detail = Detail::factory()->create();
        $picture = Picture::factory()->forDetail()->create([
            'pictureable_id' => $detail->id,
            'pictureable_type' => get_class($detail),
        ]);

        // Don't create the actual file, so it won't be found
        Storage::fake('public');

        $response = $this->deleteJson(route('picture.detachFromDetail', [$detail, $picture]));

        $response->assertNotFound();
        $response->assertJson(['error' => 'Picture file not found']);
    }

    public function test_returns_404_for_non_existent_detail(): void
    {
        $picture = Picture::factory()->forDetail()->create();

        $response = $this->deleteJson(route('picture.detachFromDetail', [fake()->uuid(), $picture]));

        $response->assertNotFound();
    }

    public function test_returns_404_for_non_existent_picture(): void
    {
        $detail = Detail::factory()->create();

        $response = $this->deleteJson(route('picture.detachFromDetail', [$detail, fake()->uuid()]));

        $response->assertNotFound();
    }

    public function test_moves_file_from_pictures_to_available_images(): void
    {
        $detail = Detail::factory()->create();
        $picture = Picture::factory()->forDetail()->create([
            'pictureable_id' => $detail->id,
            'pictureable_type' => get_class($detail),
            'path' => 'pictures/detail-image.png',
        ]);

        Storage::fake('public');
        Storage::disk('public')->put($picture->path, 'fake-image-content');

        $response = $this->deleteJson(route('picture.detachFromDetail', [$detail, $picture]));

        $response->assertOk();
        $response->assertJsonPath('available_image.path', 'images/detail-image.png');

        // Verify the file was moved
        $this->assertTrue(Storage::disk('public')->exists('images/detail-image.png'));
        $this->assertFalse(Storage::disk('public')->exists('pictures/detail-image.png'));
    }

    private function withoutAuthentication(): void
    {
        $this->user = null;
        $this->app['auth']->forgetGuards();
    }
}

<?php

namespace Tests\Feature\Api\Picture;

use App\Models\Partner;
use App\Models\Picture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DetachFromPartnerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_detach_picture_from_partner(): void
    {
        // Create a partner with a picture
        $partner = Partner::factory()->create();
        $picture = Picture::factory()->forPartner()->create([
            'pictureable_id' => $partner->id,
            'pictureable_type' => get_class($partner),
        ]);

        // Mock the storage operations
        Storage::fake('public');
        Storage::disk('public')->put($picture->path, 'fake-image-content');

        $response = $this->deleteJson(route('picture.detachFromPartner', [$partner, $picture]), [
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

        // Verify the partner no longer has the picture
        $this->assertEquals(0, $partner->pictures()->count());
    }

    public function test_can_detach_picture_with_default_comment(): void
    {
        // Create a partner with a picture
        $partner = Partner::factory()->create();
        $picture = Picture::factory()->forPartner()->create([
            'pictureable_id' => $partner->id,
            'pictureable_type' => get_class($partner),
        ]);

        // Mock the storage operations
        Storage::fake('public');
        Storage::disk('public')->put($picture->path, 'fake-image-content');

        $response = $this->deleteJson(route('picture.detachFromPartner', [$partner, $picture]));

        $response->assertOk();
        $response->assertJsonPath('message', 'Picture detached successfully');
        $response->assertJsonPath('available_image.comment', "Detached from App\\Models\\Partner ({$partner->id})");

        // Verify an available image was created with default comment
        $this->assertDatabaseHas('available_images', [
            'path' => 'images/'.basename($picture->path),
            'comment' => "Detached from App\\Models\\Partner ({$partner->id})",
        ]);
    }

    public function test_requires_authentication(): void
    {
        $this->withoutAuthentication();
        $partner = Partner::factory()->create();
        $picture = Picture::factory()->forPartner()->create([
            'pictureable_id' => $partner->id,
            'pictureable_type' => get_class($partner),
        ]);

        $response = $this->deleteJson(route('picture.detachFromPartner', [$partner, $picture]));

        $response->assertUnauthorized();
    }

    public function test_validates_picture_belongs_to_partner(): void
    {
        $partner = Partner::factory()->create();
        $otherPartner = Partner::factory()->create();
        $picture = Picture::factory()->forPartner()->create([
            'pictureable_id' => $otherPartner->id,
            'pictureable_type' => get_class($otherPartner),
        ]);

        $response = $this->deleteJson(route('picture.detachFromPartner', [$partner, $picture]));

        $response->assertUnprocessable();
        $response->assertJson(['error' => 'Picture does not belong to this model']);
    }

    public function test_validates_comment_length(): void
    {
        $partner = Partner::factory()->create();
        $picture = Picture::factory()->forPartner()->create([
            'pictureable_id' => $partner->id,
            'pictureable_type' => get_class($partner),
        ]);

        $response = $this->deleteJson(route('picture.detachFromPartner', [$partner, $picture]), [
            'comment' => str_repeat('a', 1001), // Too long
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['comment']);
    }

    public function test_returns_404_when_picture_file_not_found(): void
    {
        $partner = Partner::factory()->create();
        $picture = Picture::factory()->forPartner()->create([
            'pictureable_id' => $partner->id,
            'pictureable_type' => get_class($partner),
        ]);

        // Don't create the actual file, so it won't be found
        Storage::fake('public');

        $response = $this->deleteJson(route('picture.detachFromPartner', [$partner, $picture]));

        $response->assertNotFound();
        $response->assertJson(['error' => 'Picture file not found']);
    }

    public function test_returns_404_for_non_existent_partner(): void
    {
        $picture = Picture::factory()->forPartner()->create();

        $response = $this->deleteJson(route('picture.detachFromPartner', [fake()->uuid(), $picture]));

        $response->assertNotFound();
    }

    public function test_returns_404_for_non_existent_picture(): void
    {
        $partner = Partner::factory()->create();

        $response = $this->deleteJson(route('picture.detachFromPartner', [$partner, fake()->uuid()]));

        $response->assertNotFound();
    }

    public function test_moves_file_from_pictures_to_available_images(): void
    {
        $partner = Partner::factory()->create();
        $picture = Picture::factory()->forPartner()->create([
            'pictureable_id' => $partner->id,
            'pictureable_type' => get_class($partner),
            'path' => 'pictures/partner-logo.gif',
        ]);

        Storage::fake('public');
        Storage::disk('public')->put($picture->path, 'fake-image-content');

        $response = $this->deleteJson(route('picture.detachFromPartner', [$partner, $picture]));

        $response->assertOk();
        $response->assertJsonPath('available_image.path', 'images/partner-logo.gif');

        // Verify the file was moved
        $this->assertTrue(Storage::disk('public')->exists('images/partner-logo.gif'));
        $this->assertFalse(Storage::disk('public')->exists('pictures/partner-logo.gif'));
    }

    private function withoutAuthentication(): void
    {
        $this->user = null;
        $this->app['auth']->forgetGuards();
    }
}

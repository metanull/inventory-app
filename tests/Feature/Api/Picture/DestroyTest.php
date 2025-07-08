<?php

namespace Tests\Feature\Api\Picture;

use App\Models\Picture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_destroy_picture(): void
    {
        $picture = Picture::factory()->forItem()->create([
            'path' => 'pictures/test-image.jpg',
        ]);

        // Mock the storage to simulate file existence
        Storage::fake('public');
        Storage::disk('public')->put($picture->path, 'fake-image-content');

        $response = $this->deleteJson(route('picture.destroy', $picture));

        $response->assertNoContent();

        $this->assertDatabaseMissing('pictures', [
            'id' => $picture->id,
        ]);

        // Verify the file was deleted
        $this->assertFalse(Storage::disk('public')->exists($picture->path));
    }

    public function test_can_destroy_picture_even_if_file_does_not_exist(): void
    {
        $picture = Picture::factory()->forItem()->create([
            'path' => 'pictures/missing-image.jpg',
        ]);

        // Don't create the file, so it doesn't exist
        Storage::fake('public');

        $response = $this->deleteJson(route('picture.destroy', $picture));

        $response->assertNoContent();

        $this->assertDatabaseMissing('pictures', [
            'id' => $picture->id,
        ]);
    }

    public function test_destroys_item_picture(): void
    {
        $picture = Picture::factory()->forItem()->create();

        Storage::fake('public');
        Storage::disk('public')->put($picture->path, 'fake-image-content');

        $response = $this->deleteJson(route('picture.destroy', $picture));

        $response->assertNoContent();
        $this->assertDatabaseMissing('pictures', ['id' => $picture->id]);
    }

    public function test_destroys_detail_picture(): void
    {
        $picture = Picture::factory()->forDetail()->create();

        Storage::fake('public');
        Storage::disk('public')->put($picture->path, 'fake-image-content');

        $response = $this->deleteJson(route('picture.destroy', $picture));

        $response->assertNoContent();
        $this->assertDatabaseMissing('pictures', ['id' => $picture->id]);
    }

    public function test_destroys_partner_picture(): void
    {
        $picture = Picture::factory()->forPartner()->create();

        Storage::fake('public');
        Storage::disk('public')->put($picture->path, 'fake-image-content');

        $response = $this->deleteJson(route('picture.destroy', $picture));

        $response->assertNoContent();
        $this->assertDatabaseMissing('pictures', ['id' => $picture->id]);
    }

    public function test_returns_404_for_non_existent_picture(): void
    {
        $response = $this->deleteJson(route('picture.destroy', fake()->uuid()));

        $response->assertNotFound();
    }

    public function test_requires_authentication(): void
    {
        $this->withoutAuthentication();
        $picture = Picture::factory()->forItem()->create();

        $response = $this->deleteJson(route('picture.destroy', $picture));

        $response->assertUnauthorized();
    }

    public function test_removes_picture_from_parent_model(): void
    {
        $picture = Picture::factory()->forItem()->create();
        $item = $picture->pictureable;

        $this->assertEquals(1, $item->pictures()->count());

        Storage::fake('public');
        Storage::disk('public')->put($picture->path, 'fake-image-content');

        $response = $this->deleteJson(route('picture.destroy', $picture));

        $response->assertNoContent();
        $this->assertEquals(0, $item->fresh()->pictures()->count());
    }

    private function withoutAuthentication(): void
    {
        $this->user = null;
        $this->app['auth']->forgetGuards();
    }
}

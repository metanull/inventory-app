<?php

namespace Tests\Feature\Api\Picture;

use App\Models\Picture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DownloadTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_download_picture(): void
    {
        $picture = Picture::factory()->forItem()->create([
            'path' => 'pictures/test-download.jpg',
            'upload_name' => 'test-download.jpg',
        ]);

        Storage::fake('public');
        Storage::disk('public')->put($picture->path, 'fake-image-content');

        $response = $this->get(route('picture.download', $picture));

        $response->assertOk();
        $response->assertHeader('Content-Disposition', 'attachment; filename=test-download.jpg');
    }

    public function test_download_returns_404_for_missing_file(): void
    {
        $picture = Picture::factory()->forItem()->create([
            'path' => 'pictures/missing-file.jpg',
        ]);

        Storage::fake('public');
        // Don't create the file

        $response = $this->get(route('picture.download', $picture));

        $response->assertNotFound();
    }

    public function test_download_requires_authentication(): void
    {
        $this->withoutAuthentication();
        $picture = Picture::factory()->forItem()->create();

        $response = $this->get(route('picture.download', $picture));

        // Download routes redirect to login when unauthenticated (302) rather than returning 401
        $response->assertRedirect();
    }

    public function test_download_returns_404_for_non_existent_picture(): void
    {
        $response = $this->get(route('picture.download', fake()->uuid()));

        $response->assertNotFound();
    }

    public function test_download_uses_original_filename(): void
    {
        $picture = Picture::factory()->forItem()->create([
            'path' => 'pictures/hashed-name.jpg',
            'upload_name' => 'Original Photo.jpg',
        ]);

        Storage::fake('public');
        Storage::disk('public')->put($picture->path, 'fake-image-content');

        $response = $this->get(route('picture.download', $picture));

        $response->assertOk();
        $response->assertHeader('Content-Disposition', 'attachment; filename="Original Photo.jpg"');
    }

    private function withoutAuthentication(): void
    {
        $this->user = null;
        $this->app['auth']->forgetGuards();
    }
}

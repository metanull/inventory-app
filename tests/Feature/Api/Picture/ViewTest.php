<?php

namespace Tests\Feature\Api\Picture;

use App\Models\Picture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ViewTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_view_picture(): void
    {
        $picture = Picture::factory()->forItem()->create([
            'path' => 'pictures/test-view.jpg',
            'upload_name' => 'test-view.jpg',
            'upload_mime_type' => 'image/jpeg',
        ]);

        Storage::fake('public');
        Storage::disk('public')->put($picture->path, 'fake-image-content');

        $response = $this->get(route('picture.view', $picture));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/jpeg');
        $response->assertHeader('Content-Disposition', 'inline; filename="test-view.jpg"');

        // Check that Cache-Control header contains both directives (order doesn't matter)
        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('max-age=3600', $cacheControl);

        $this->assertEquals('fake-image-content', $response->getContent());
    }

    public function test_view_returns_404_for_missing_file(): void
    {
        $picture = Picture::factory()->forItem()->create([
            'path' => 'pictures/missing-file.jpg',
        ]);

        Storage::fake('public');
        // Don't create the file

        $response = $this->get(route('picture.view', $picture));

        $response->assertNotFound();
    }

    public function test_view_requires_authentication(): void
    {
        $this->withoutAuthentication();
        $picture = Picture::factory()->forItem()->create();

        $response = $this->get(route('picture.view', $picture));

        // View routes redirect to login when unauthenticated (302) rather than returning 401
        $response->assertRedirect();
    }

    public function test_view_returns_404_for_non_existent_picture(): void
    {
        $response = $this->get(route('picture.view', fake()->uuid()));

        $response->assertNotFound();
    }

    public function test_view_returns_correct_mime_type(): void
    {
        $picture = Picture::factory()->forItem()->create([
            'path' => 'pictures/test-png.png',
            'upload_name' => 'test-png.png',
            'upload_mime_type' => 'image/png',
        ]);

        Storage::fake('public');
        Storage::disk('public')->put($picture->path, 'fake-png-content');

        $response = $this->get(route('picture.view', $picture));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
        $response->assertHeader('Content-Disposition', 'inline; filename="test-png.png"');
    }

    public function test_view_includes_caching_headers(): void
    {
        $picture = Picture::factory()->forItem()->create([
            'path' => 'pictures/cached-image.jpg',
            'upload_mime_type' => 'image/jpeg',
        ]);

        Storage::fake('public');
        Storage::disk('public')->put($picture->path, 'fake-content');

        $response = $this->get(route('picture.view', $picture));

        $response->assertOk();
        $response->assertHeader('Cache-Control');

        // Check that both cache control directives are present
        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('max-age=3600', $cacheControl);
    }

    private function withoutAuthentication(): void
    {
        $this->user = null;
        $this->app['auth']->forgetGuards();
    }
}

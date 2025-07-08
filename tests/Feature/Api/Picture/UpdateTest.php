<?php

namespace Tests\Feature\Api\Picture;

use App\Models\Picture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_update_picture(): void
    {
        $picture = Picture::factory()->forItem()->create([
            'internal_name' => 'Original Name',
            'copyright_text' => 'Original Copyright',
        ]);

        $response = $this->putJson(route('picture.update', $picture), [
            'internal_name' => 'Updated Name',
            'backward_compatibility' => 'legacy-456',
            'copyright_text' => 'Updated Copyright',
            'copyright_url' => 'https://updated.example.com',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.internal_name', 'Updated Name');
        $response->assertJsonPath('data.backward_compatibility', 'legacy-456');
        $response->assertJsonPath('data.copyright_text', 'Updated Copyright');
        $response->assertJsonPath('data.copyright_url', 'https://updated.example.com');

        $this->assertDatabaseHas('pictures', [
            'id' => $picture->id,
            'internal_name' => 'Updated Name',
            'backward_compatibility' => 'legacy-456',
            'copyright_text' => 'Updated Copyright',
            'copyright_url' => 'https://updated.example.com',
        ]);
    }

    public function test_can_update_partial_fields(): void
    {
        $picture = Picture::factory()->forItem()->create([
            'internal_name' => 'Original Name',
            'copyright_text' => 'Original Copyright',
            'copyright_url' => 'https://original.example.com',
        ]);

        $response = $this->putJson(route('picture.update', $picture), [
            'internal_name' => 'Updated Name Only',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.internal_name', 'Updated Name Only');
        $response->assertJsonPath('data.copyright_text', 'Original Copyright');
        $response->assertJsonPath('data.copyright_url', 'https://original.example.com');
    }

    public function test_can_clear_optional_fields(): void
    {
        $picture = Picture::factory()->forItem()->create([
            'internal_name' => 'Test Picture',
            'backward_compatibility' => 'legacy-123',
            'copyright_text' => 'Some Copyright',
            'copyright_url' => 'https://example.com',
        ]);

        $response = $this->putJson(route('picture.update', $picture), [
            'internal_name' => 'Test Picture',
            'backward_compatibility' => null,
            'copyright_text' => null,
            'copyright_url' => null,
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.backward_compatibility', null);
        $response->assertJsonPath('data.copyright_text', null);
        $response->assertJsonPath('data.copyright_url', null);

        $this->assertDatabaseHas('pictures', [
            'id' => $picture->id,
            'backward_compatibility' => null,
            'copyright_text' => null,
            'copyright_url' => null,
        ]);
    }

    public function test_requires_internal_name(): void
    {
        $picture = Picture::factory()->forItem()->create();

        $response = $this->putJson(route('picture.update', $picture), [
            'copyright_text' => 'Updated Copyright',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_validates_copyright_url_format(): void
    {
        $picture = Picture::factory()->forItem()->create();

        $response = $this->putJson(route('picture.update', $picture), [
            'internal_name' => 'Test Picture',
            'copyright_url' => 'not-a-valid-url',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['copyright_url']);
    }

    public function test_cannot_update_readonly_fields(): void
    {
        $picture = Picture::factory()->forItem()->create([
            'path' => 'original-path.jpg',
            'upload_name' => 'original.jpg',
            'upload_extension' => 'jpg',
            'upload_mime_type' => 'image/jpeg',
            'upload_size' => 1024,
        ]);

        $response = $this->putJson(route('picture.update', $picture), [
            'internal_name' => 'Updated Name',
            'path' => 'hacked-path.jpg',
            'upload_name' => 'hacked.jpg',
            'upload_extension' => 'png',
            'upload_mime_type' => 'image/png',
            'upload_size' => 9999,
            'pictureable_type' => 'App\\Models\\Item',
            'pictureable_id' => fake()->uuid(),
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'path',
            'upload_name',
            'upload_extension',
            'upload_mime_type',
            'upload_size',
            'pictureable_type',
            'pictureable_id',
        ]);
    }

    public function test_validates_field_lengths(): void
    {
        $picture = Picture::factory()->forItem()->create();

        $response = $this->putJson(route('picture.update', $picture), [
            'internal_name' => str_repeat('a', 256), // Too long
            'backward_compatibility' => str_repeat('b', 256), // Too long
            'copyright_text' => str_repeat('c', 1001), // Too long
            'copyright_url' => 'https://example.com/'.str_repeat('d', 300), // Too long
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'internal_name',
            'backward_compatibility',
            'copyright_text',
            'copyright_url',
        ]);
    }

    public function test_returns_404_for_non_existent_picture(): void
    {
        $response = $this->putJson(route('picture.update', fake()->uuid()), [
            'internal_name' => 'Updated Name',
        ]);

        $response->assertNotFound();
    }

    public function test_requires_authentication(): void
    {
        $this->withoutAuthentication();
        $picture = Picture::factory()->forItem()->create();

        $response = $this->putJson(route('picture.update', $picture), [
            'internal_name' => 'Updated Name',
        ]);

        $response->assertUnauthorized();
    }

    private function withoutAuthentication(): void
    {
        $this->user = null;
        $this->app['auth']->forgetGuards();
    }
}

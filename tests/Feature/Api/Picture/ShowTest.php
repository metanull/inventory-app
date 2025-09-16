<?php

namespace Tests\Feature\Api\Picture;

use App\Models\Picture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_show_picture(): void
    {
        $picture = Picture::factory()->forItem()->create([
            'internal_name' => 'Test Picture',
            'copyright_text' => 'Test Copyright',
            'copyright_url' => 'https://example.com',
        ]);

        $response = $this->getJson(route('picture.show', $picture));

        $response->assertOk();
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

        $response->assertJsonPath('data.id', $picture->id);
        $response->assertJsonPath('data.internal_name', 'Test Picture');
        $response->assertJsonPath('data.copyright_text', 'Test Copyright');
        $response->assertJsonPath('data.copyright_url', 'https://example.com');
    }

    public function test_can_show_item_picture(): void
    {
        $picture = Picture::factory()->forItem()->create();

        $response = $this->getJson(route('picture.show', $picture));

        $response->assertOk();
        $response->assertJsonPath('data.pictureable_type', 'App\\Models\\Item');
        $response->assertJsonPath('data.pictureable_id', $picture->pictureable_id);
    }

    public function test_can_show_detail_picture(): void
    {
        $picture = Picture::factory()->forDetail()->create();

        $response = $this->getJson(route('picture.show', $picture));

        $response->assertOk();
        $response->assertJsonPath('data.pictureable_type', 'App\\Models\\Detail');
        $response->assertJsonPath('data.pictureable_id', $picture->pictureable_id);
    }

    public function test_can_show_partner_picture(): void
    {
        $picture = Picture::factory()->forPartner()->create();

        $response = $this->getJson(route('picture.show', $picture));

        $response->assertOk();
        $response->assertJsonPath('data.pictureable_type', 'App\\Models\\Partner');
        $response->assertJsonPath('data.pictureable_id', $picture->pictureable_id);
    }

    public function test_returns_404_for_non_existent_picture(): void
    {
        $response = $this->getJson(route('picture.show', fake()->uuid()));

        $response->assertNotFound();
    }

    public function test_requires_authentication(): void
    {
        $this->withoutAuthentication();
        $picture = Picture::factory()->forItem()->create();

        $response = $this->getJson(route('picture.show', $picture));

        $response->assertUnauthorized();
    }

    public function test_shows_all_picture_metadata(): void
    {
        $picture = Picture::factory()->forItem()->create([
            'internal_name' => 'Complete Picture',
            'backward_compatibility' => 'legacy-123',
            'copyright_text' => 'All rights reserved',
            'copyright_url' => 'https://copyright.example.com',
            'path' => 'pictures/complete-image.jpg',
            'upload_name' => 'complete-image.jpg',
            'upload_extension' => 'jpg',
            'upload_mime_type' => 'image/jpeg',
            'upload_size' => 1024000,
        ]);

        $response = $this->getJson(route('picture.show', $picture));

        $response->assertOk();
        $response->assertJsonPath('data.internal_name', 'Complete Picture');
        $response->assertJsonPath('data.backward_compatibility', 'legacy-123');
        $response->assertJsonPath('data.copyright_text', 'All rights reserved');
        $response->assertJsonPath('data.copyright_url', 'https://copyright.example.com');
        $response->assertJsonPath('data.path', 'pictures/complete-image.jpg');
        $response->assertJsonPath('data.upload_name', 'complete-image.jpg');
        $response->assertJsonPath('data.upload_extension', 'jpg');
        $response->assertJsonPath('data.upload_mime_type', 'image/jpeg');
        $response->assertJsonPath('data.upload_size', 1024000);
    }

    public function test_show_returns_the_default_structure_without_relations(): void
    {
        $picture = Picture::factory()->forItem()->create();

        $response = $this->getJson(route('picture.show', $picture));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'internal_name',
                'backward_compatibility',
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
    }

    public function test_show_returns_the_expected_structure_with_all_relations_loaded(): void
    {
        $picture = Picture::factory()->forItem()->create();

        $response = $this->getJson(route('picture.show', [$picture, 'include' => 'pictureable,translations']));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'internal_name',
                'backward_compatibility',
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
    }

    private function withoutAuthentication(): void
    {
        $this->user = null;
        $this->app['auth']->forgetGuards();
    }
}

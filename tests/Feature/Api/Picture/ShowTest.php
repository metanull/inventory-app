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

    public function test_show_allows_authenticated_users(): void
    {
        $picture = Picture::factory()->create();
        $response = $this->getJson(route('picture.show', $picture));
        $response->assertOk();
    }

    public function test_show_returns_ok_on_success(): void
    {
        $picture = Picture::factory()->create();
        $response = $this->getJson(route('picture.show', $picture));
        $response->assertOk();
    }

    public function test_show_returns_not_found_when_record_does_not_exist(): void
    {
        $response = $this->getJson(route('picture.show', 'non-existent-id'));
        $response->assertNotFound();
    }

    public function test_show_returns_the_expected_structure(): void
    {
        $picture = Picture::factory()->create();
        $response = $this->getJson(route('picture.show', $picture));
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
                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_show_returns_the_expected_data(): void
    {
        $picture = Picture::factory()->create();
        $response = $this->getJson(route('picture.show', $picture));
        $response->assertOk();
        $response->assertJsonPath('data.id', $picture->id);
        $response->assertJsonPath('data.internal_name', $picture->internal_name);
        $response->assertJsonPath('data.backward_compatibility', $picture->backward_compatibility);
    }
}

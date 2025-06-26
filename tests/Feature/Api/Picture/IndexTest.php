<?php

namespace Tests\Feature\Api\Picture;

use App\Models\Picture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_index_allows_authenticated_users(): void
    {
        $response = $this->getJson(route('picture.index'));
        $response->assertOk();
    }

    public function test_index_returns_ok_on_success(): void
    {
        $response = $this->getJson(route('picture.index'));
        $response->assertOk();
    }

    public function test_index_returns_the_expected_structure(): void
    {
        Picture::factory()->create();
        $response = $this->getJson(route('picture.index'));
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
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
                ]
            ]
        ]);
    }

    public function test_index_returns_the_expected_data(): void
    {
        $picture = Picture::factory()->create();
        $response = $this->getJson(route('picture.index'));
        $response->assertOk();
        $response->assertJsonPath('data.0.id', $picture->id);
        $response->assertJsonPath('data.0.internal_name', $picture->internal_name);
    }
}

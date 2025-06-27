<?php

namespace Tests\Feature\Api\ImageUpload;

use App\Models\ImageUpload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
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

        Storage::fake('local');
        Event::fake();
        Http::fake();
    }

    public function test_index_allows_authenticated_users(): void
    {
        $response = $this->getJson(route('image-upload.index'));
        $response->assertOk();
    }

    public function test_index_returns_ok_when_no_data(): void
    {
        $response = $this->getJson(route('image-upload.index'));
        $response->assertOk();
    }

    public function test_index_returns_an_empty_array_when_no_data(): void
    {
        $response = $this->getJson(route('image-upload.index'));
        $response->assertJson([
            'data' => [],
        ]);
    }

    public function test_index_returns_the_expected_structure(): void
    {
        $response = $this->getJson(route('image-upload.index'));
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'path',
                    'name',
                    'extension',
                    'mime_type',
                    'size',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    public function test_index_returns_the_expected_data(): void
    {
        $imageUpload1 = ImageUpload::factory()->create();
        $imageUpload2 = ImageUpload::factory()->create();

        $response = $this->getJson(route('image-upload.index'));
        $response->assertJson([
            'data' => [
                [
                    'id' => $imageUpload1->id,
                    'path' => $imageUpload1->path,
                    'name' => $imageUpload1->name,
                    'extension' => $imageUpload1->extension,
                    'mime_type' => $imageUpload1->mime_type,
                    'size' => $imageUpload1->size,
                ],
                [
                    'id' => $imageUpload2->id,
                    'path' => $imageUpload2->path,
                    'name' => $imageUpload2->name,
                    'extension' => $imageUpload2->extension,
                    'mime_type' => $imageUpload2->mime_type,
                    'size' => $imageUpload2->size,
                ],
            ],
        ]);
    }
}

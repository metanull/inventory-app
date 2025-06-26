<?php

namespace Tests\Feature\Api\Picture;

use App\Models\Picture;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        Storage::fake('local');
    }

    public function test_store_allows_authenticated_users(): void
    {
        $file = UploadedFile::fake()->image('test.jpg');
        $data = Picture::factory()->make()->except(['id', 'path', 'upload_name', 'upload_extension', 'upload_mime_type', 'upload_size']);
        $data['file'] = $file;

        $response = $this->postJson(route('picture.store'), $data);
        $response->assertCreated();
    }

    public function test_store_creates_a_row(): void
    {
        $file = UploadedFile::fake()->image('test.jpg');
        $data = Picture::factory()->make()->except(['id', 'path', 'upload_name', 'upload_extension', 'upload_mime_type', 'upload_size']);
        $data['file'] = $file;

        $response = $this->postJson(route('picture.store'), $data);
        $response->assertCreated();
        $this->assertDatabaseHas('pictures', [
            'internal_name' => $data['internal_name'],
            'backward_compatibility' => $data['backward_compatibility'],
            'copyright_text' => $data['copyright_text'],
            'copyright_url' => $data['copyright_url'],
        ]);
    }

    public function test_store_returns_created_on_success(): void
    {
        $file = UploadedFile::fake()->image('test.jpg');
        $data = Picture::factory()->make()->except(['id', 'path', 'upload_name', 'upload_extension', 'upload_mime_type', 'upload_size']);
        $data['file'] = $file;

        $response = $this->postJson(route('picture.store'), $data);
        $response->assertCreated();
    }

    public function test_store_returns_unprocessable_entity_when_input_is_invalid(): void
    {
        $data = Picture::factory()->make()->except(['internal_name', 'file']); // missing required fields

        $response = $this->postJson(route('picture.store'), $data);
        $response->assertUnprocessable();
    }

    public function test_store_returns_the_expected_structure(): void
    {
        $file = UploadedFile::fake()->image('test.jpg');
        $data = Picture::factory()->make()->except(['id', 'path', 'upload_name', 'upload_extension', 'upload_mime_type', 'upload_size']);
        $data['file'] = $file;

        $response = $this->postJson(route('picture.store'), $data);
        $response->assertCreated();
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
            ]
        ]);
    }

    public function test_store_returns_the_expected_data(): void
    {
        $file = UploadedFile::fake()->image('test.jpg');
        $data = Picture::factory()->make()->except(['id', 'path', 'upload_name', 'upload_extension', 'upload_mime_type', 'upload_size']);
        $data['file'] = $file;

        $response = $this->postJson(route('picture.store'), $data);
        $response->assertCreated();
        $response->assertJsonPath('data.internal_name', $data['internal_name']);
        $response->assertJsonPath('data.backward_compatibility', $data['backward_compatibility']);
        $response->assertJsonPath('data.copyright_text', $data['copyright_text']);
        $response->assertJsonPath('data.copyright_url', $data['copyright_url']);
    }

    public function test_store_validates_its_input(): void
    {
        $data = Picture::factory()->make()->except(['internal_name', 'file']); // missing required fields

        $response = $this->postJson(route('picture.store'), $data);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name', 'file']);
    }
}

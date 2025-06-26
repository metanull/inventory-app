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

    /**
     * Factory: test factory.
     */
    public function test_factory()
    {
        $picture = Picture::factory()->create();
        $this->assertInstanceOf(Picture::class, $picture);
        $this->assertDatabaseHas('pictures', ['id' => $picture->id]);
    }

    /**
     * Authentication: store allows authenticated users.
     */
    public function test_store_allows_authenticated_users()
    {
        $file = UploadedFile::fake()->image('test.jpg');
        $data = Picture::factory()->make()->except(['id', 'path', 'upload_name', 'upload_extension', 'upload_mime_type', 'upload_size']);
        $data['file'] = $file;

        $response = $this->postJson(route('picture.store'), $data);
        $response->assertCreated();
    }

    /**
     * Process: store creates a row.
     */
    public function test_store_creates_a_row()
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

    /**
     * Response: store returns created on success.
     */
    public function test_store_returns_created_on_success()
    {
        $file = UploadedFile::fake()->image('test.jpg');
        $data = Picture::factory()->make()->except(['id', 'path', 'upload_name', 'upload_extension', 'upload_mime_type', 'upload_size']);
        $data['file'] = $file;

        $response = $this->postJson(route('picture.store'), $data);
        $response->assertCreated();
    }

    /**
     * Response: store returns unprocessable entity when input is invalid.
     */
    public function test_store_returns_unprocessable_entity_when_input_is_invalid()
    {
        $data = Picture::factory()->make()->except(['internal_name', 'file']); // missing required fields

        $response = $this->postJson(route('picture.store'), $data);
        $response->assertUnprocessable();
    }

    /**
     * Response: store returns the expected structure.
     */
    public function test_store_returns_the_expected_structure()
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

    /**
     * Response: store returns the expected data.
     */
    public function test_store_returns_the_expected_data()
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

    /**
     * Validation: store validates its input.
     */
    public function test_store_validates_its_input()
    {
        $data = Picture::factory()->make()->except(['internal_name', 'file']); // missing required fields

        $response = $this->postJson(route('picture.store'), $data);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name', 'file']);
    }
}

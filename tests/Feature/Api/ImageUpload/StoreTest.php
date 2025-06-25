<?php

namespace Tests\Feature\Api\ImageUpload;

use App\Events\ImageUploadEvent;
use App\Models\ImageUpload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
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
        Event::fake();
    }

    public function test_store_allows_authenticated_users(): void
    {
        $response = $this->postJson(route('image-upload.store'), [
            'file' => UploadedFile::fake()->image('test.jpg'),
        ]);
        $response->assertCreated();
    }

    public function test_store_validates_its_input(): void
    {
        $response = $this->postJson(route('image-upload.store'), [
            'file' => 'not-a-file',
        ]);
        $response->assertJsonValidationErrors(['file']);
    }

    public function test_store_returns_unprocessable_when_input_is_invalid(): void
    {
        $response = $this->postJson(route('image-upload.store'), [
            'file' => 'not-a-file',
        ]);
        $response->assertUnprocessable();
    }

    public function test_store_inserts_a_row_in_the_database(): void
    {
        $response = $this->postJson(route('image-upload.store'), [
            'file' => UploadedFile::fake()->image('test.jpg'),
        ]);

        $this->assertDatabaseHas('image_uploads', [
            'name' => 'test.jpg',
            'mime_type' => 'image/jpeg',
        ]);
    }

    public function test_store_returns_created_on_success(): void
    {
        $response = $this->postJson(route('image-upload.store'), [
            'file' => UploadedFile::fake()->image('test.jpg'),
        ]);
        $response->assertCreated();
    }

    public function test_store_returns_the_expected_structure(): void
    {
        $response = $this->postJson(route('image-upload.store'), [
            'file' => UploadedFile::fake()->image('test.jpg'),
        ]);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'path',
                'name',
                'extension',
                'mime_type',
                'size',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_store_returns_the_expected_data(): void
    {
        $response = $this->postJson(route('image-upload.store'), [
            'file' => UploadedFile::fake()->image('test.jpg'),
        ]);
        $response->assertJson([
            'data' => [
                'name' => 'test.jpg',
                'mime_type' => 'image/jpeg',
            ],
        ]);
    }

    public function test_store_dispatches_imageuploadevent_event(): void
    {
        $this->postJson(route('image-upload.store'), [
            'file' => UploadedFile::fake()->image('test.jpg'),
        ]);
        Event::assertDispatched(
            ImageUploadEvent::class,
            function (ImageUploadEvent $event) {
                return $event->imageUpload instanceof ImageUpload;
            }
        );
    }
}

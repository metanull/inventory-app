<?php

namespace Tests\Feature;

use App\Events\ImageUploadEvent;
use App\Listeners\ImageUploadListener;
use App\Models\ImageUpload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Tests\TestCase;

class ImageUploadTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_image_upload_factory(): void
    {
        Storage::fake('local');
        Event::fake();

        $imageUpload = ImageUpload::factory()->create();

        $this->assertDatabaseHas('image_uploads', [
            'id' => $imageUpload->id,
            'path' => $imageUpload->path,
            'name' => $imageUpload->name,
            'extension' => $imageUpload->extension,
            'mime_type' => $imageUpload->mime_type,
            'size' => $imageUpload->size,
        ]);
    }

    public function test_image_upload_factory_create_a_file(): void
    {
        Storage::fake('local');
        Event::fake();

        $imageUpload = ImageUpload::factory()->create();

        $this->assertFileExists(Storage::disk('local')->path($imageUpload->path.'/'.$imageUpload->name));
    }

    public function test_api_authentication_index_forbids_anonymous_access(): void
    {
        $response = $this->getJson(route('image-upload.index'));
        $response->assertUnauthorized();
    }

    public function test_api_authentication_index_allows_authenticated_users(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson(route('image-upload.index'));
        $response->assertOk();
    }

    public function test_api_authentication_show_forbids_anonymous_access(): void
    {
        Storage::fake('local');
        Event::fake();

        $imageUpload = ImageUpload::factory()->create();
        $response = $this->getJson(route('image-upload.show', $imageUpload->id));
        $response->assertUnauthorized();
    }

    public function test_api_authentication_show_allows_authenticated_users(): void
    {
        Storage::fake('local');
        Event::fake();

        $user = User::factory()->create();
        $imageUpload = ImageUpload::factory()->create();

        $this->actingAs($user);
        $response = $this->getJson(route('image-upload.show', $imageUpload->id));
        $response->assertOk();
    }

    public function test_api_authentication_store_forbids_anonymous_access(): void
    {
        Storage::fake('local');
        Event::fake();

        $response = $this->postJson(route('image-upload.store'), [
            'file' => UploadedFile::fake()->image('test.jpg'),
        ]);
        $response->assertUnauthorized();
    }

    public function test_api_authentication_store_allows_authenticated_users(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Storage::fake('local');
        Event::fake();

        $response = $this->postJson(route('image-upload.store'), [
            'file' => UploadedFile::fake()->image('test.jpg'),
        ]);
        $response->assertCreated();
    }

    public function test_api_route_update_is_not_found(): void
    {
        $this->expectException(RouteNotFoundException::class);

        $this->putJson(route('image-upload.update', 'non-existent-id'), [
            'file' => UploadedFile::fake()->image('updated.jpg'),
        ]);
    }

    public function test_api_authentication_destroy_forbids_anonymous_access(): void
    {
        Storage::fake('local');
        Event::fake();

        $imageUpload = ImageUpload::factory()->create();
        $response = $this->deleteJson(route('image-upload.destroy', $imageUpload->id));
        $response->assertUnauthorized();
    }

    public function test_api_authentication_destroy_allows_authenticated_users(): void
    {
        Storage::fake('local');
        Event::fake();

        $user = User::factory()->create();
        $imageUpload = ImageUpload::factory()->create();

        $this->actingAs($user);
        $response = $this->deleteJson(route('image-upload.destroy', $imageUpload->id));
        $response->assertNoContent();
    }

    public function test_api_response_show_returns_not_found_when_not_found(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson(route('image-upload.show', 'non-existent-id'));
        $response->assertNotFound();
    }

    public function test_api_response_show_returns_the_expected_structure(): void
    {
        Storage::fake('local');
        Event::fake();

        $user = User::factory()->create();
        $imageUpload = ImageUpload::factory()->create();

        $this->actingAs($user);
        $response = $this->getJson(route('image-upload.show', $imageUpload->id));
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

    public function test_api_response_show_returns_the_expected_data(): void
    {
        Storage::fake('local');
        Event::fake();

        $user = User::factory()->create();
        $imageUpload = ImageUpload::factory()->create();

        $this->actingAs($user);
        $response = $this->getJson(route('image-upload.show', $imageUpload->id));
        $response->assertJson([
            'data' => [
                'id' => $imageUpload->id,
                'path' => $imageUpload->path,
                'name' => $imageUpload->name,
                'extension' => $imageUpload->extension,
                'mime_type' => $imageUpload->mime_type,
                'size' => $imageUpload->size,
            ],
        ]);
    }

    public function test_api_response_index_returns_ok_when_no_data(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson(route('image-upload.index'));
        $response->assertOk();
    }

    public function test_api_response_index_returns_an_empty_array_when_no_data(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson(route('image-upload.index'));
        $response->assertJson([
            'data' => [],
        ]);
    }

    public function test_api_response_index_returns_the_expected_structure(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

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

    public function test_api_response_index_returns_the_expected_data(): void
    {
        Storage::fake('local');
        Event::fake();

        $user = User::factory()->create();
        $this->actingAs($user);

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

    public function test_api_validation_store_validates_its_input(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Storage::fake('local');
        Event::fake();

        $response = $this->postJson(route('image-upload.store'), [
            'file' => 'not-a-file',
        ]);
        $response->assertJsonValidationErrors(['file']);
    }

    public function test_api_response_store_returns_unprocessable_when_input_is_invalid(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Storage::fake('local');
        Event::fake();

        $response = $this->postJson(route('image-upload.store'), [
            'file' => 'not-a-file',
        ]);
        $response->assertUnprocessable();
    }

    public function test_api_process_store_inserts_a_row_in_the_database(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Storage::fake('local');
        Event::fake();

        $response = $this->postJson(route('image-upload.store'), [
            'file' => UploadedFile::fake()->image('test.jpg'),
        ]);

        $this->assertDatabaseHas('image_uploads', [
            'name' => 'test.jpg',
            'mime_type' => 'image/jpeg',
        ]);
    }

    public function test_api_response_store_returns_created_on_success(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Storage::fake('local');
        Event::fake();

        $response = $this->postJson(route('image-upload.store'), [
            'file' => UploadedFile::fake()->image('test.jpg'),
        ]);
        $response->assertCreated();
    }

    public function test_api_response_store_returns_the_expected_structure(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Storage::fake('local');
        Event::fake();

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

    public function test_api_response_store_returns_the_expected_data(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Storage::fake('local');
        Event::fake();

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

    public function test_api_process_imageuploadlistener_listener_is_registered_for_imageuploadevent_event(): void
    {
        Event::fake();
        Event::assertListening(
            expectedEvent: ImageUploadEvent::class,
            expectedListener: ImageUploadListener::class,
        );
    }

    public function test_api_process_store_dispatches_imageuploadevent_event(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Storage::fake('local');
        Event::fake();

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

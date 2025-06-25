<?php

namespace Tests\Feature;

use App\Events\AvailableImageEvent;
use App\Events\ImageUploadEvent;
use App\Listeners\AvailableImageListener;
use App\Listeners\ImageUploadListener;
use App\Models\AvailableImage;
use App\Models\ImageUpload;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Tests\TestCase;

class AvailableImageTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_available_image_factory(): void
    {
        Storage::fake('public');
        Event::fake();

        $availableImage = AvailableImage::factory()->create();

        $this->assertDatabaseHas('available_images', [
            'id' => $availableImage->id,
            'path' => $availableImage->path,
            'comment' => $availableImage->comment,
        ]);
    }

    public function test_available_image_factory_creates_a_file(): void
    {
        Storage::fake('public');
        Event::fake();

        $availableImage = AvailableImage::factory()->create();

        $this->assertFileExists(Storage::disk('public')->path($availableImage->path), 'The path of the available image should exist.');
    }

    public function test_api_authentication_index_forbids_anonymous_access(): void
    {
        $response = $this->getJson(route('available-image.index'));
        $response->assertUnauthorized();
    }

    public function test_api_authentication_index_allows_authenticated_users(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('available-image.index'));
        $response->assertOk();
    }

    public function test_api_authentication_show_forbids_anonymous_access(): void
    {
        Storage::fake('public');
        Event::fake();

        $availableImage = AvailableImage::factory()->create();

        $response = $this->getJson(route('available-image.show', $availableImage->id));
        $response->assertUnauthorized();
    }

    public function test_api_authentication_show_allows_authenticated_users(): void
    {
        Storage::fake('public');
        Event::fake();

        $availableImage = AvailableImage::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson(route('available-image.show', $availableImage->id));
        $response->assertOk();
    }

    public function test_api_route_store_is_not_found(): void
    {
        Storage::fake('public');
        Event::fake();
        
        $availableImage = AvailableImage::factory()->make()->toArray();
        $this->expectException(RouteNotFoundException::class);

        $response = $this->postJson(route('available-image.store'), $availableImage);
    }

    public function test_api_authentication_update_forbids_anonymous_access(): void
    {
        Storage::fake('public');
        Event::fake();

        $availableImage = AvailableImage::factory()->create();

        $response = $this->putJson(route('available-image.update', $availableImage->id), [
            'comment' => fake()->sentence(),
        ]);
        $response->assertUnauthorized();
    }

    public function test_api_authentication_update_allows_authenticated_users(): void
    {
        Storage::fake('public');
        Event::fake();

        $availableImage = AvailableImage::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('available-image.update', $availableImage->id), [
                'comment' => fake()->sentence(),
            ]);
        $response->assertOk();
    }

    public function test_api_authentication_destroy_forbids_anonymous_access(): void
    {
        Storage::fake('public');
        Event::fake();

        $availableImage = AvailableImage::factory()->create();

        $response = $this->deleteJson(route('available-image.destroy', $availableImage->id));
        $response->assertUnauthorized();
    }

    public function test_api_authentication_destroy_allows_authenticated_users(): void
    {
        Storage::fake('public');
        Event::fake();

        $availableImage = AvailableImage::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->deleteJson(route('available-image.destroy', $availableImage->id));
        $response->assertNoContent();
    }

    public function test_api_authentication_download_forbids_anonymous_access(): void
    {
        Storage::fake('public');
        Event::fake();

        $availableImage = AvailableImage::factory()->create();

        $response = $this->getJson(route('available-image.download', $availableImage->id));
        $response->assertUnauthorized();
    }

    public function test_api_authentication_download_allows_authenticated_users(): void
    {
        Storage::fake('public');
        Event::fake();

        $availableImage = AvailableImage::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson(route('available-image.download', $availableImage->id));
        $response->assertOk();
    }

    public function test_api_response_show_returns_not_found_when_not_found(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('available-image.show', 'non-existent-id'));
        $response->assertNotFound();
    }

    public function test_api_response_show_returns_the_expected_structure(): void
    {
        Storage::fake('public');
        Event::fake();

        $availableImage = AvailableImage::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson(route('available-image.show', $availableImage->id));
        $response->assertJsonStructure([
            'data' => [
                'id',
                'path',
                'comment',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_api_response_show_returns_the_expected_data(): void
    {
        Storage::fake('public');
        Event::fake();

        $availableImage = AvailableImage::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson(route('available-image.show', $availableImage->id));
        $response->assertJsonPath('data.id', $availableImage->id)
            ->assertJsonPath('data.path', $availableImage->path)
            ->assertJsonPath('data.comment', $availableImage->comment);
    }

    public function test_api_response_index_returns_ok_when_no_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson(route('available-image.index'));
        $response->assertOk();
    }

    public function test_api_response_index_returns_an_empty_array_when_no_data()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson(route('available-image.index'));
        $response->assertJsonCount(0, 'data');
    }

    public function test_api_response_index_returns_the_expected_structure(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson(route('available-image.index'));
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'path',
                    'comment',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    public function test_api_response_index_returns_the_expected_data(): void
    {
        Storage::fake('public');
        Event::fake();

        $availableImage1 = AvailableImage::factory()->create();
        $availableImage2 = AvailableImage::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson(route('available-image.index'));
        $response->assertJsonPath('data.0.id', $availableImage1->id)
            ->assertJsonPath('data.0.path', $availableImage1->path)
            ->assertJsonPath('data.0.comment', $availableImage1->comment)
            ->assertJsonPath('data.1.id', $availableImage2->id)
            ->assertJsonPath('data.1.path', $availableImage2->path)
            ->assertJsonPath('data.1.comment', $availableImage2->comment);
    }

    public function test_api_validation_update_validates_its_input(): void
    {
        Storage::fake('public');
        Event::fake();

        $availableImage = AvailableImage::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('available-image.update', $availableImage->id), [
                'path' => 'prohibited field',   // This field should not be allowed
                'comment' => null,              // This field allows null
            ]);
        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['path']);
    }

    public function test_api_process_update_updates_a_row(): void
    {
        Storage::fake('public');
        Event::fake();

        $availableImage = AvailableImage::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->putJson(route('available-image.update', $availableImage->id), [
                'comment' => 'Updated comment',
            ]);
        $this->assertDatabaseHas('available_images', [
            'id' => $availableImage->id,
            'comment' => 'Updated comment',
        ]);
    }

    public function test_api_response_update_returns_ok_on_success(): void
    {
        Storage::fake('public');
        Event::fake();

        $availableImage = AvailableImage::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('available-image.update', $availableImage->id), [
                'comment' => 'Updated comment',
            ]);
        $response->assertOk();
    }

    public function test_api_response_update_returns_unprocessable_when_input_is_invalid(): void
    {
        Storage::fake('public');
        Event::fake();

        $availableImage = AvailableImage::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('available-image.update', $availableImage->id), [
                'path' => 'invalid path', // This should not be allowed
                'comment' => null,        // This field allows null
            ]);
        $response->assertUnprocessable();
    }

    public function test_api_response_update_returns_not_found_response_when_not_found(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->putJson(route('available-image.update', 'non-existent-id'), [
                'comment' => fake()->sentence(),
            ]);
        $response->assertNotFound();
    }

    public function test_api_response_update_returns_the_expected_structure(): void
    {
        Storage::fake('public');
        Event::fake();

        $availableImage = AvailableImage::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('available-image.update', $availableImage->id), [
                'comment' => 'Updated comment',
            ]);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'path',
                'comment',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_api_response_update_returns_the_expected_data(): void
    {
        Storage::fake('public');
        Event::fake();

        $availableImage = AvailableImage::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('available-image.update', $availableImage->id), [
                'comment' => 'Updated comment',
            ]);
        $response->assertJsonPath('data.id', $availableImage->id)
            ->assertJsonPath('data.path', $availableImage->path)
            ->assertJsonPath('data.comment', 'Updated comment');
    }

    public function test_api_response_destroy_returns_not_found_response_when_not_found(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->deleteJson(route('available-image.destroy', 'non-existent-id'));
        $response->assertNotFound();
    }

    public function test_api_process_destroy_deletes_a_row(): void
    {
        Storage::fake('public');
        Event::fake();

        $availableImage = AvailableImage::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->deleteJson(route('available-image.destroy', $availableImage->id));
        $this->assertDatabaseMissing('available_images', [
            'id' => $availableImage->id,
        ]);
    }

    public function test_api_response_destroy_returns_no_content_on_success(): void
    {
        Storage::fake('public');
        Event::fake();

        $availableImage = AvailableImage::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->deleteJson(route('available-image.destroy', $availableImage->id));
        $response->assertNoContent();
    }

    public function test_api_process_availableimagelistener_listener_is_registered_for_availableimageevent_event(): void
    {
        Event::fake();
        Event::assertListening(
            expectedEvent: AvailableImageEvent::class,
            expectedListener: AvailableImageListener::class,
        );
    }

    public function test_api_process_imageuploadlistener_listener_dispatches_availableimageevent_event(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Event::fake();

        Storage::disk('local')->makeDirectory('image_uploads');
        Storage::disk('public')->makeDirectory('images');

        $imageUpload = ImageUpload::factory()->create();
        $imageUploadEvent = new ImageUploadEvent($imageUpload);
        $imageUploadListener = new ImageUploadListener;
        $imageUploadListener->handle($imageUploadEvent);
        Event::assertDispatched(
            AvailableImageEvent::class,
            function (AvailableImageEvent $event) {
                return $event->availableImage instanceof AvailableImage;
            }
        );
    }

    public function test_api_process_availableimagelistener_removes_uploaded_image_from_local_disk(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Event::fake();

        Storage::disk('local')->makeDirectory('image_uploads');
        Storage::disk('public')->makeDirectory('images');

        $imageUpload = ImageUpload::factory()->create();
        $imageUploadEvent = new ImageUploadEvent($imageUpload);
        $imageUploadListener = new ImageUploadListener;
        $imageUploadRelativePath = $imageUpload->path.'/'.$imageUpload->name;
        $imageUploadListener->handle($imageUploadEvent);
        $this->assertFileExists(Storage::disk('local')->path($imageUploadRelativePath));
    }

    public function test_api_process_availableimagelistener_creates_an_image_on_the_public_disk(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Event::fake();

        Storage::disk('local')->makeDirectory('image_uploads');
        Storage::disk('public')->makeDirectory('images');

        // Create a fake image upload
        $imageUpload = ImageUpload::factory()->create();

        // Dispatch the AvailableImageEvent, using the fake uploaded image's path as source
        $availableImage = AvailableImage::factory()->create([
            'path' => $imageUpload->path.'/'.$imageUpload->name,
            'id' => $imageUpload->id,
        ]);
        $availableImageEvent = new AvailableImageEvent($availableImage);
        $availableImageListener = new AvailableImageListener;
        $availableImageListener->handle($availableImageEvent);

        // The Listener should have created a new image in the public disk, and have updated
        // the path of the AvailableImage model to point to the new image in the public disk.
        $availableImage->refresh();
        $this->assertFileExists(Storage::disk('public')->path($availableImage->path));
    }

    public function test_api_process_availableimagelistener_updates_the_image_path_in_database(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Event::fake();

        Storage::disk('local')->makeDirectory('image_uploads');
        Storage::disk('public')->makeDirectory('images');

        // Create a fake image upload
        $imageUpload = ImageUpload::factory()->create();

        // Dispatch the AvailableImageEvent, using the fake uploaded image's path as source
        $availableImage = AvailableImage::factory()->create([
            'path' => $imageUpload->path.'/'.$imageUpload->name,
            'id' => $imageUpload->id,
        ]);
        $availableImageEvent = new AvailableImageEvent($availableImage);
        $availableImageListener = new AvailableImageListener;
        $availableImageListener->handle($availableImageEvent);

        // The Listener should have created a new image in the public disk, and have updated
        // the path of the AvailableImage model to point to the new image in the public disk.
        $this->assertDatabaseHas('available_images', [
            'id' => $availableImage->id,
            'path' => $availableImage->path,
        ]);
    }

    public function test_api_process_availableimagelistener_removes_the_uploaded_image_from_the_private_disk(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Event::fake();

        Storage::disk('local')->makeDirectory('image_uploads');
        Storage::disk('public')->makeDirectory('images');

        // Create a fake image upload
        $imageUpload = ImageUpload::factory()->create();

        // Dispatch the AvailableImageEvent, using the fake uploaded image's path as source
        $availableImage = AvailableImage::factory()->create([
            'path' => $imageUpload->path.'/'.$imageUpload->name,
            'id' => $imageUpload->id,
        ]);
        $availableImageEvent = new AvailableImageEvent($availableImage);
        $availableImageListener = new AvailableImageListener;
        $availableImageListener->handle($availableImageEvent);

        // The Listener should have created a new image in the public disk, and have updated
        // the path of the AvailableImage model to point to the new image in the public disk.
        $availableImage->refresh();
        $this->assertFileDoesNotExist(Storage::disk('local')->path($imageUpload->path.'/'.$imageUpload->name));
    }
}

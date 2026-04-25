<?php

namespace Tests\Filament\Resources;

use App\Enums\Permission;
use App\Events\AvailableImageEvent;
use App\Events\ImageUploadEvent;
use App\Filament\Resources\AvailableImageResource\Pages\EditAvailableImage;
use App\Filament\Resources\AvailableImageResource\Pages\ListAvailableImage;
use App\Listeners\AvailableImageListener;
use App\Listeners\ImageUploadListener;
use App\Models\AvailableImage;
use App\Models\ImageUpload;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AvailableImageResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_users_can_render_available_image_list_and_view_pages(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $user = $this->createCrudUser();
        $availableImage = AvailableImage::factory()->create([
            'comment' => 'A beautiful artefact photo',
        ]);

        $this->actingAs($user)->get('/admin/available-images')
            ->assertOk()
            ->assertSee('Available Images');

        $this->actingAs($user)->get("/admin/available-images/{$availableImage->getKey()}")
            ->assertOk();
    }

    public function test_list_page_has_inline_upload_table_header_action(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $user = $this->createCrudUser();

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(ListAvailableImage::class)
            ->assertTableHeaderActionsExistInOrder(['upload']);
    }

    public function test_imageuploadevent_dispatches_correctly_when_imageupload_model_is_created(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Storage::disk('local')->makeDirectory('image_uploads');
        Event::fake();

        $imageUpload = ImageUpload::factory()->create();

        ImageUploadEvent::dispatch($imageUpload);

        Event::assertDispatched(ImageUploadEvent::class, function (ImageUploadEvent $event) use ($imageUpload) {
            return $event->imageUpload->id === $imageUpload->id;
        });
    }

    public function test_available_image_metadata_can_be_edited(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $user = $this->createCrudUser();
        $availableImage = AvailableImage::factory()->create([
            'comment' => 'Original comment',
        ]);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(EditAvailableImage::class, [
                'record' => $availableImage->getRouteKey(),
            ])
            ->assertFormSet([
                'comment' => 'Original comment',
            ])
            ->fillForm([
                'comment' => 'Updated comment',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('available_images', [
            'id' => $availableImage->id,
            'comment' => 'Updated comment',
        ]);
    }

    public function test_available_image_can_be_deleted(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Storage::disk('public')->makeDirectory('images');

        $user = $this->createCrudUser();
        $availableImage = AvailableImage::factory()->create();

        $imagePath = trim(config('localstorage.available.images.directory'), '/').'/'.$availableImage->path;
        Storage::disk('public')->put($imagePath, 'fake-image-data');

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(ListAvailableImage::class)
            ->callTableAction(DeleteAction::class, $availableImage)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('available_images', [
            'id' => $availableImage->id,
        ]);
    }

    public function test_imageuploadevent_is_registered_with_imageuploadlistener(): void
    {
        Event::fake();
        Event::assertListening(
            expectedEvent: ImageUploadEvent::class,
            expectedListener: ImageUploadListener::class,
        );
    }

    public function test_availableimageevent_is_registered_with_availableimagelistener(): void
    {
        Event::fake();
        Event::assertListening(
            expectedEvent: AvailableImageEvent::class,
            expectedListener: AvailableImageListener::class,
        );
    }

    public function test_filament_image_view_routes_are_registered(): void
    {
        $this->assertNotNull(route('filament.admin.available-image.view', ['availableImage' => 'test-id']));
        $this->assertNotNull(route('filament.admin.available-image.download', ['availableImage' => 'test-id']));
        $this->assertNotNull(route('filament.admin.item-image.view', ['item' => 'item-id', 'itemImage' => 'image-id']));
        $this->assertNotNull(route('filament.admin.item-image.download', ['item' => 'item-id', 'itemImage' => 'image-id']));
        $this->assertNotNull(route('filament.admin.collection-image.view', ['collection' => 'col-id', 'collectionImage' => 'image-id']));
        $this->assertNotNull(route('filament.admin.collection-image.download', ['collection' => 'col-id', 'collectionImage' => 'image-id']));
        $this->assertNotNull(route('filament.admin.partner-image.view', ['partner' => 'par-id', 'partnerImage' => 'image-id']));
        $this->assertNotNull(route('filament.admin.partner-image.download', ['partner' => 'par-id', 'partnerImage' => 'image-id']));
    }

    public function test_imageupload_listener_processes_file_and_creates_available_image(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        Storage::disk('local')->makeDirectory('image_uploads');
        Storage::disk('public')->makeDirectory('images');

        $imageUpload = ImageUpload::factory()->create();
        $minimalJpeg = base64_decode('/9j/4AAQSkZJRgABAQEAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCdABmX/9k=');
        Storage::disk('local')->put($imageUpload->path, $minimalJpeg);

        $uploadId = $imageUpload->id;

        $listener = new ImageUploadListener;
        $listener->handle(new ImageUploadEvent($imageUpload));

        $this->assertDatabaseMissing('image_uploads', ['id' => $uploadId]);

        $availableImage = AvailableImage::find($uploadId);
        $this->assertNotNull($availableImage);
        $this->assertNotEmpty($availableImage->path);
    }

    protected function createCrudUser(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::VIEW_DATA->value,
            Permission::CREATE_DATA->value,
            Permission::UPDATE_DATA->value,
            Permission::DELETE_DATA->value,
        ]);

        return $user;
    }

    protected function setCurrentPanel(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }
}

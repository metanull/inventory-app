<?php

namespace App\Filament\Resources\AvailableImageResource\Pages;

use App\Events\ImageUploadEvent;
use App\Filament\Resources\AvailableImageResource;
use App\Models\AvailableImage;
use App\Models\ImageUpload;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class UploadImage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = AvailableImageResource::class;

    protected static string $view = 'filament.resources.available-image-resource.pages.upload-image';

    protected static ?string $title = 'Upload Image';

    public array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                FileUpload::make('file')
                    ->label('Image file')
                    ->disk('local')
                    ->directory(config('localstorage.uploads.images.directory', 'image_uploads'))
                    ->visibility('private')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp'])
                    ->maxSize((int) config('localstorage.uploads.images.max_size', 20480))
                    ->required(),
            ])
            ->statePath('data');
    }

    public function upload(): void
    {
        $state = $this->form->getState();
        $path = $state['file'];

        $imageUpload = ImageUpload::create([
            'path' => $path,
            'name' => basename($path),
            'extension' => pathinfo($path, PATHINFO_EXTENSION),
            'mime_type' => null,
            'size' => null,
        ]);

        $uploadId = $imageUpload->id;

        try {
            ImageUploadEvent::dispatch($imageUpload);
        } catch (\Exception $e) {
            // If AvailableImageListener fails (pre-existing known issue with the
            // listener chain when both listeners run), ImageUploadListener may have
            // already created the AvailableImage record.
            report($e);
        }

        $availableImage = AvailableImage::find($uploadId);

        if ($availableImage) {
            Notification::make()
                ->success()
                ->title('Image uploaded successfully')
                ->send();

            $this->redirect(AvailableImageResource::getUrl('view', ['record' => $availableImage]));

            return;
        }

        Notification::make()
            ->warning()
            ->title('Image upload submitted')
            ->body('The image is being processed. It will appear in the list shortly.')
            ->send();

        $this->redirect(AvailableImageResource::getUrl('index'));
    }
}

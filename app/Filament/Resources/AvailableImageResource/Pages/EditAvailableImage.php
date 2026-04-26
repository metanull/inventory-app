<?php

namespace App\Filament\Resources\AvailableImageResource\Pages;

use App\Filament\Resources\AvailableImageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditAvailableImage extends EditRecord
{
    protected static string $resource = AvailableImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->before(function (): void {
                    $record = $this->getRecord();
                    $disk = $record->imageDisk();
                    $path = $record->imageStoragePath();

                    if (Storage::disk($disk)->exists($path)) {
                        Storage::disk($disk)->delete($path);
                    }
                }),
        ];
    }
}

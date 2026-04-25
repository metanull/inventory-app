<?php

namespace App\Filament\Resources\AvailableImageResource\Pages;

use App\Filament\Resources\AvailableImageResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewAvailableImage extends ViewRecord
{
    protected static string $resource = AvailableImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
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

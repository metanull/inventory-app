<?php

namespace App\Filament\Resources\AvailableImageResource\Pages;

use App\Filament\Resources\AvailableImageResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListAvailableImages extends ListRecords
{
    protected static string $resource = AvailableImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload')
                ->label('Upload image')
                ->icon('heroicon-o-arrow-up-tray')
                ->url(AvailableImageResource::getUrl('upload')),
        ];
    }
}

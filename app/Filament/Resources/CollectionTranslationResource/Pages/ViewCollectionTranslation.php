<?php

namespace App\Filament\Resources\CollectionTranslationResource\Pages;

use App\Filament\Resources\CollectionTranslationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCollectionTranslation extends ViewRecord
{
    protected static string $resource = CollectionTranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\CollectionTranslationResource\Pages;

use App\Filament\Resources\CollectionTranslationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCollectionTranslation extends EditRecord
{
    protected static string $resource = CollectionTranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

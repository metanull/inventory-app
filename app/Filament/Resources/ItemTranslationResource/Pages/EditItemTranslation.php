<?php

namespace App\Filament\Resources\ItemTranslationResource\Pages;

use App\Filament\Resources\ItemTranslationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditItemTranslation extends EditRecord
{
    protected static string $resource = ItemTranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

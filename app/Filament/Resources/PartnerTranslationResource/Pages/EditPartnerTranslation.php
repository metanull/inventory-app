<?php

namespace App\Filament\Resources\PartnerTranslationResource\Pages;

use App\Filament\Resources\PartnerTranslationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPartnerTranslation extends EditRecord
{
    protected static string $resource = PartnerTranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

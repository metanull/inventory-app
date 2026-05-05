<?php

namespace App\Filament\Resources\PartnerTranslationResource\Pages;

use App\Filament\Resources\PartnerTranslationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPartnerTranslation extends ViewRecord
{
    protected static string $resource = PartnerTranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}

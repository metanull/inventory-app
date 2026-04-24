<?php

namespace App\Filament\Resources\GlossaryResource\Pages;

use App\Filament\Resources\GlossaryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewGlossary extends ViewRecord
{
    protected static string $resource = GlossaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}

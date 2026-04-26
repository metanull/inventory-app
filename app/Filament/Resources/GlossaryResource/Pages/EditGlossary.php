<?php

namespace App\Filament\Resources\GlossaryResource\Pages;

use App\Filament\Resources\GlossaryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGlossary extends EditRecord
{
    protected static string $resource = GlossaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

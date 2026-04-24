<?php

namespace App\Filament\Resources\GlossaryResource\Pages;

use App\Filament\Resources\GlossaryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGlossary extends ListRecords
{
    protected static string $resource = GlossaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

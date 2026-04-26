<?php

namespace App\Filament\Resources\ContextResource\Pages;

use App\Filament\Resources\ContextResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContext extends ListRecords
{
    protected static string $resource = ContextResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\ContextResource\Pages;

use App\Filament\Resources\ContextResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewContext extends ViewRecord
{
    protected static string $resource = ContextResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}

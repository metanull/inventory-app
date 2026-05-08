<?php

namespace App\Filament\Resources\ItemItemLinkResource\Pages;

use App\Filament\Resources\ItemItemLinkResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewItemItemLink extends ViewRecord
{
    protected static string $resource = ItemItemLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

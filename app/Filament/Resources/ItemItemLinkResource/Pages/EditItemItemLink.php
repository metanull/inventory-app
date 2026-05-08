<?php

namespace App\Filament\Resources\ItemItemLinkResource\Pages;

use App\Filament\Resources\ItemItemLinkResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditItemItemLink extends EditRecord
{
    protected static string $resource = ItemItemLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\ItemResource\Pages;

use App\Filament\Concerns\HasFullWidthRelationManagerContentTab;
use App\Filament\Resources\ItemResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewItem extends ViewRecord
{
    use HasFullWidthRelationManagerContentTab;

    protected static string $resource = ItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }

    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabLabel(): ?string
    {
        return 'Item';
    }
}

<?php

namespace App\Filament\Resources\PartnerResource\Pages;

use App\Filament\Concerns\HasFullWidthRelationManagerContentTab;
use App\Filament\Resources\PartnerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPartner extends ViewRecord
{
    use HasFullWidthRelationManagerContentTab;

    protected static string $resource = PartnerResource::class;

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
        return 'Partner';
    }
}

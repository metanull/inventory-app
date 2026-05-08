<?php

namespace App\Filament\Resources\TimelineResource\Pages;

use App\Filament\Concerns\HasFullWidthRelationManagerContentTab;
use App\Filament\Resources\TimelineResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTimeline extends ViewRecord
{
    use HasFullWidthRelationManagerContentTab;

    protected static string $resource = TimelineResource::class;

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
        return 'Timeline';
    }
}

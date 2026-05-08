<?php

namespace App\Filament\Resources\TimelineEventResource\Pages;

use App\Filament\Resources\TimelineEventResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTimelineEvent extends ViewRecord
{
    protected static string $resource = TimelineEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\TimelineEventResource\Pages;

use App\Filament\Concerns\RedirectsToViewAfterSave;
use App\Filament\Resources\TimelineEventResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTimelineEvent extends EditRecord
{
    use RedirectsToViewAfterSave;

    protected static string $resource = TimelineEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

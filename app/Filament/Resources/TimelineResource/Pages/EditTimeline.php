<?php

namespace App\Filament\Resources\TimelineResource\Pages;

use App\Filament\Concerns\RedirectsToViewAfterSave;
use App\Filament\Resources\TimelineResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTimeline extends EditRecord
{
    use RedirectsToViewAfterSave;

    protected static string $resource = TimelineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

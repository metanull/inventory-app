<?php

namespace App\Filament\Resources\ProjectResource\Pages;

use App\Filament\Concerns\RedirectsToViewAfterSave;
use App\Filament\Resources\ProjectResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProject extends EditRecord
{
    use RedirectsToViewAfterSave;

    protected static string $resource = ProjectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

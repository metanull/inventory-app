<?php

namespace App\Filament\Resources\ContextResource\Pages;

use App\Filament\Concerns\RedirectsToViewAfterSave;
use App\Filament\Resources\ContextResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContext extends EditRecord
{
    use RedirectsToViewAfterSave;

    protected static string $resource = ContextResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

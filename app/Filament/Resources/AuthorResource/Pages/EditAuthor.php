<?php

namespace App\Filament\Resources\AuthorResource\Pages;

use App\Filament\Concerns\RedirectsToViewAfterSave;
use App\Filament\Resources\AuthorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAuthor extends EditRecord
{
    use RedirectsToViewAfterSave;

    protected static string $resource = AuthorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}

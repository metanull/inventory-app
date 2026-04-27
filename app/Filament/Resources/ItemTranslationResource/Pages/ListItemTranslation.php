<?php

namespace App\Filament\Resources\ItemTranslationResource\Pages;

use App\Filament\Resources\ItemTranslationResource;
use App\Models\Context;
use App\Models\Language;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListItemTranslation extends ListRecords
{
    protected static string $resource = ItemTranslationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->fillForm(fn (): array => [
                    'language_id' => Language::default()->first()?->id,
                    'context_id' => Context::default()->first()?->id,
                ]),
        ];
    }
}

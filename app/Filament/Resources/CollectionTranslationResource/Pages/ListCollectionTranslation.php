<?php

namespace App\Filament\Resources\CollectionTranslationResource\Pages;

use App\Filament\Resources\CollectionTranslationResource;
use App\Models\Context;
use App\Models\Language;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCollectionTranslation extends ListRecords
{
    protected static string $resource = CollectionTranslationResource::class;

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

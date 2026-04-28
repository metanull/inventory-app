<?php

namespace App\Filament\Resources\PartnerTranslationResource\Pages;

use App\Filament\Resources\PartnerTranslationResource;
use App\Models\Context;
use App\Models\Language;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPartnerTranslation extends ListRecords
{
    protected static string $resource = PartnerTranslationResource::class;

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

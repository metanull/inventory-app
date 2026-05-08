<?php

namespace App\Filament\Resources\ItemTranslationResource\RelationManagers;

use App\Filament\Resources\ItemTranslationResource;
use App\Filament\Resources\RelationManagers\BaseSiblingTranslationsRelationManager;

class SiblingTranslationsRelationManager extends BaseSiblingTranslationsRelationManager
{
    protected static function translationResource(): string
    {
        return ItemTranslationResource::class;
    }

    protected static function translationTitleAttribute(): string
    {
        return 'name';
    }
}

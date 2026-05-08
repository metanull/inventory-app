<?php

namespace App\Filament\Resources\CollectionTranslationResource\RelationManagers;

use App\Filament\Resources\CollectionTranslationResource;
use App\Filament\Resources\RelationManagers\BaseSiblingTranslationsRelationManager;

class SiblingTranslationsRelationManager extends BaseSiblingTranslationsRelationManager
{
    protected static function translationResource(): string
    {
        return CollectionTranslationResource::class;
    }

    protected static function translationTitleAttribute(): string
    {
        return 'title';
    }
}

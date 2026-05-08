<?php

namespace App\Filament\Resources\PartnerTranslationResource\RelationManagers;

use App\Filament\Resources\PartnerTranslationResource;
use App\Filament\Resources\RelationManagers\BaseSiblingTranslationsRelationManager;

class SiblingTranslationsRelationManager extends BaseSiblingTranslationsRelationManager
{
    protected static function translationResource(): string
    {
        return PartnerTranslationResource::class;
    }

    protected static function translationTitleAttribute(): string
    {
        return 'name';
    }
}

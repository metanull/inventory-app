<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Exhibition Model
 *
 * Represents an Exhibition collection, which contains Themes (to be implemented next).
 * Supports translation and partner relationships.
 */
class Exhibition extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'backward_compatibility',
        'internal_name',
    ];

    /**
     * Get all translations for this exhibition.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ExhibitionTranslation::class);
    }

    /**
     * Partners relationship (polymorphic many-to-many with pivot for level).
     */
    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class, 'collection_partner', 'collection_id', 'partner_id')
            ->wherePivot('collection_type', '=', 'exhibition')
            ->withPivot('level');
    }

    /**
     * Get all themes for this exhibition.
     */
    public function themes(): HasMany
    {
        return $this->hasMany(Theme::class);
    }

    /**
     * Get the default context translation for this exhibition in a specific language.
     */
    public function getDefaultTranslation(string $languageId): ?ExhibitionTranslation
    {
        return $this->translations()
            ->defaultContext()
            ->forLanguage($languageId)
            ->first();
    }

    /**
     * Get a contextualized translation for this exhibition.
     */
    public function getContextualizedTranslation(string $languageId, string $contextId): ?ExhibitionTranslation
    {
        return $this->translations()
            ->forLanguage($languageId)
            ->forContext($contextId)
            ->first();
    }

    /**
     * Get translation with fallback logic: try specific context, then default context.
     */
    public function getTranslationWithFallback(string $languageId, ?string $contextId = null): ?ExhibitionTranslation
    {
        if ($contextId) {
            $translation = $this->getContextualizedTranslation($languageId, $contextId);
            if ($translation) {
                return $translation;
            }
        }

        return $this->getDefaultTranslation($languageId);
    }

    /**
     * The unique identifiers for the model.
     */
    public function uniqueIds(): array
    {
        return ['id'];
    }
}

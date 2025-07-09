<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Detail extends Model
{
    /** @use HasFactory<\Database\Factories\DetailFactory> */
    use HasFactory;

    use HasUuids;

    protected $with = [
        'item',
    ];

    protected $fillable = [
        // 'id',
        'item_id',
        'internal_name',
        'backward_compatibility',
    ];

    /**
     * The item associated with the Detail.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get all translations for this detail.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(DetailTranslation::class);
    }

    /**
     * Get all pictures attached to this detail.
     */
    public function pictures(): MorphMany
    {
        return $this->morphMany(Picture::class, 'pictureable');
    }

    /**
     * Get all galleries that include this detail.
     */
    public function galleries(): MorphToMany
    {
        return $this->morphToMany(Gallery::class, 'galleryable')
            ->withPivot(['order', 'backward_compatibility'])
            ->withTimestamps();
    }

    /**
     * Get the default context translation for this detail in a specific language.
     */
    public function getDefaultTranslation(string $languageId): ?DetailTranslation
    {
        return $this->translations()
            ->defaultContext()
            ->forLanguage($languageId)
            ->first();
    }

    /**
     * Get a contextualized translation for this detail.
     */
    public function getContextualizedTranslation(string $languageId, string $contextId): ?DetailTranslation
    {
        return $this->translations()
            ->forLanguage($languageId)
            ->forContext($contextId)
            ->first();
    }

    /**
     * Get translation with fallback logic: try specific context, then default context.
     */
    public function getTranslationWithFallback(string $languageId, ?string $contextId = null): ?DetailTranslation
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
     * Get the columns that should automatically receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['id'];
    }
}

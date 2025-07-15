<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

/**
 * Picture Model
 *
 * Represents images that can be attached to Items, Details, or Partners.
 * Uses polymorphic relationships to support multiple parent types.
 */
class Picture extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'internal_name',
        'backward_compatibility',
        'copyright_text',
        'copyright_url',
        'path',
        'upload_name',
        'upload_extension',
        'upload_mime_type',
        'upload_size',
        'pictureable_type',
        'pictureable_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'upload_size' => 'integer',
    ];

    /**
     * Get the columns that should automatically receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['id'];
    }

    /**
     * Get the parent pictureable model (Item, Detail, or Partner).
     */
    public function pictureable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get all translations for this picture.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(PictureTranslation::class);
    }

    /**
     * Get the default context translation for this picture in a specific language.
     */
    public function getDefaultTranslation(string $languageId): ?PictureTranslation
    {
        return $this->translations()
            ->defaultContext()
            ->forLanguage($languageId)
            ->first();
    }

    /**
     * Get a contextualized translation for this picture.
     */
    public function getContextualizedTranslation(string $languageId, string $contextId): ?PictureTranslation
    {
        return $this->translations()
            ->forLanguage($languageId)
            ->forContext($contextId)
            ->first();
    }

    /**
     * Get translation with fallback logic: try specific context, then default context.
     */
    public function getTranslationWithFallback(string $languageId, ?string $contextId = null): ?PictureTranslation
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
     * Get all themes/subthemes this picture is attached to.
     */
    public function themes(): MorphToMany
    {
        return $this->morphedByMany(Theme::class, 'pictureable');
    }
}

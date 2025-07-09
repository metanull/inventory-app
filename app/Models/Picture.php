<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Picture Model
 *
 * Represents images that can be attached to Items, Details, or Partners.
 * Uses polymorphic relationships to support multiple parent types.
 *
 * @property string $id Primary key (UUID)
 * @property string $internal_name Internal name for the picture
 * @property string|null $backward_compatibility Legacy system reference
 * @property string|null $copyright_text Copyright text
 * @property string|null $copyright_url Copyright URL
 * @property string $path Path to the image file
 * @property string $upload_name Original filename
 * @property string $upload_extension File extension
 * @property string $upload_mime_type MIME type
 * @property int $upload_size File size in bytes
 * @property string $pictureable_type Type of parent model (Item, Detail, Partner)
 * @property string $pictureable_id ID of parent model
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
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
}

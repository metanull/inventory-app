<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Internationalization Model
 *
 * Stores internationalized text content for contextualizations.
 * Each internationalization record contains text fields in a specific language
 * for a specific contextualization.
 *
 * @property string $id Primary key (UUID)
 * @property string $contextualization_id Foreign key to contextualization
 * @property string $language_id Foreign key to language
 * @property string $name Item name
 * @property string|null $alternate_name Alternate name for the item
 * @property string $description Long description of the item
 * @property string|null $type Type of item (e.g., 'Carpet', 'Manuscript')
 * @property string|null $holder Holding institution or museum
 * @property string|null $owner Current owner of the item
 * @property string|null $initial_owner Initial owner of the item
 * @property string|null $dates Historical period or dates
 * @property string|null $location Location of the item
 * @property string|null $dimensions Size or dimensions of the item
 * @property string|null $place_of_production Where the item was crafted
 * @property string|null $method_for_datation Method used for dating
 * @property string|null $method_for_provenance Method used for provenance
 * @property string|null $obtention How the item was obtained
 * @property string|null $bibliography Bibliographical references
 * @property array|null $extra Additional unstructured data
 * @property string|null $author_id Author who wrote the original text
 * @property string|null $text_copy_editor_id Author who reviewed the text
 * @property string|null $translator_id Author who translated the text
 * @property string|null $translation_copy_editor_id Author who reviewed the translation
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string|null $backward_compatibility Legacy system reference
 */
class Internationalization extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'contextualization_id',
        'language_id',
        'name',
        'alternate_name',
        'description',
        'type',
        'holder',
        'owner',
        'initial_owner',
        'dates',
        'location',
        'dimensions',
        'place_of_production',
        'method_for_datation',
        'method_for_provenance',
        'obtention',
        'bibliography',
        'extra',
        'author_id',
        'text_copy_editor_id',
        'translator_id',
        'translation_copy_editor_id',
        'backward_compatibility',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'extra' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the columns that should receive a unique identifier.
     */
    public function uniqueIds(): array
    {
        return ['id'];
    }

    /**
     * The contextualization this internationalization belongs to
     */
    public function contextualization(): BelongsTo
    {
        return $this->belongsTo(Contextualization::class);
    }

    /**
     * The language of this internationalization
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * The author who wrote the original text
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'author_id');
    }

    /**
     * The author who reviewed the text
     */
    public function textCopyEditor(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'text_copy_editor_id');
    }

    /**
     * The author who translated the text
     */
    public function translator(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'translator_id');
    }

    /**
     * The author who reviewed the translation
     */
    public function translationCopyEditor(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'translation_copy_editor_id');
    }

    /**
     * Scope to get internationalizations in the default language
     */
    public function scopeInDefaultLanguage(Builder $query): Builder
    {
        return $query->whereHas('language', function (Builder $q) {
            $q->default();
        });
    }

    /**
     * Scope to get internationalizations in English
     */
    public function scopeInEnglish(Builder $query): Builder
    {
        return $query->where('language_id', 'eng');
    }

    /**
     * Create an internationalization using the default language
     */
    public static function createWithDefaultLanguage(array $attributes): self
    {
        $defaultLanguage = Language::default()->first();
        if (! $defaultLanguage) {
            throw new \Exception('No default language found');
        }

        $attributes['language_id'] = $defaultLanguage->id;

        return static::create($attributes);
    }

    /**
     * Create an internationalization using English
     */
    public static function createWithEnglish(array $attributes): self
    {
        $attributes['language_id'] = 'eng';

        return static::create($attributes);
    }

    // Accessors and Mutators to ensure null values instead of empty strings
    public function getAlternateNameAttribute($value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function setAlternateNameAttribute($value): void
    {
        $this->attributes['alternate_name'] = $value === '' ? null : $value;
    }

    public function getTypeAttribute($value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function setTypeAttribute($value): void
    {
        $this->attributes['type'] = $value === '' ? null : $value;
    }

    public function getHolderAttribute($value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function setHolderAttribute($value): void
    {
        $this->attributes['holder'] = $value === '' ? null : $value;
    }

    public function getOwnerAttribute($value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function setOwnerAttribute($value): void
    {
        $this->attributes['owner'] = $value === '' ? null : $value;
    }

    public function getInitialOwnerAttribute($value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function setInitialOwnerAttribute($value): void
    {
        $this->attributes['initial_owner'] = $value === '' ? null : $value;
    }

    public function getDatesAttribute($value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function setDatesAttribute($value): void
    {
        $this->attributes['dates'] = $value === '' ? null : $value;
    }

    public function getLocationAttribute($value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function setLocationAttribute($value): void
    {
        $this->attributes['location'] = $value === '' ? null : $value;
    }

    public function getDimensionsAttribute($value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function setDimensionsAttribute($value): void
    {
        $this->attributes['dimensions'] = $value === '' ? null : $value;
    }

    public function getPlaceOfProductionAttribute($value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function setPlaceOfProductionAttribute($value): void
    {
        $this->attributes['place_of_production'] = $value === '' ? null : $value;
    }

    public function getMethodForDatationAttribute($value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function setMethodForDatationAttribute($value): void
    {
        $this->attributes['method_for_datation'] = $value === '' ? null : $value;
    }

    public function getMethodForProvenanceAttribute($value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function setMethodForProvenanceAttribute($value): void
    {
        $this->attributes['method_for_provenance'] = $value === '' ? null : $value;
    }

    public function getObtentionAttribute($value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function setObtentionAttribute($value): void
    {
        $this->attributes['obtention'] = $value === '' ? null : $value;
    }

    public function getBibliographyAttribute($value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function setBibliographyAttribute($value): void
    {
        $this->attributes['bibliography'] = $value === '' ? null : $value;
    }

    public function getBackwardCompatibilityAttribute($value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function setBackwardCompatibilityAttribute($value): void
    {
        $this->attributes['backward_compatibility'] = $value === '' ? null : $value;
    }
}

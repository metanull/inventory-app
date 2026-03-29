<?php

namespace App\Models;

use App\Traits\HasJsonFields;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DynastyTranslation Model
 *
 * Represents language-specific translations for Dynasties.
 * Contains translated dynasty names, areas, and historical descriptions.
 */
class DynastyTranslation extends Model
{
    use HasFactory, HasJsonFields, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dynasty_translations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'dynasty_id',
        'language_id',
        'name',
        'also_known_as',
        'area',
        'history',
        'date_description_ah',
        'date_description_ad',
        'backward_compatibility',
        'extra',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'extra' => 'object',
    ];

    /**
     * Get the unique identifiers for the model.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['id'];
    }

    /**
     * Get the extra field decoded as an associative array.
     */
    protected function extraDecoded(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->normalizedJson('extra')
        );
    }

    /**
     * Get the dynasty that owns the translation.
     */
    public function dynasty(): BelongsTo
    {
        return $this->belongsTo(Dynasty::class);
    }

    /**
     * Get the language of the translation.
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * Scope a query to only include translations for a specific language.
     *
     * @param  Builder  $query
     * @param  string  $languageId
     * @return Builder
     */
    public function scopeForLanguage($query, $languageId)
    {
        return $query->where('language_id', $languageId);
    }
}

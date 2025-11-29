<?php

namespace App\Models;

use App\Traits\HasJsonFields;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * LanguageTranslation Model
 *
 * Represents translations for language names displayed in different languages.
 * For example, "English" displayed as "Anglais" in French.
 */
class LanguageTranslation extends Model
{
    use HasFactory, HasJsonFields, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'language_translations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'language_id',
        'display_language_id',
        'name',
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
     * Get the language being translated.
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'language_id');
    }

    /**
     * Get the language this translation is displayed in.
     */
    public function displayLanguage(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'display_language_id');
    }

    /**
     * Scope a query to only include translations displayed in a specific language.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $displayLanguageId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForDisplayLanguage($query, $displayLanguageId)
    {
        return $query->where('display_language_id', $displayLanguageId);
    }
}

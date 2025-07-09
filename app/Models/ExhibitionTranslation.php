<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ExhibitionTranslation Model
 *
 * Represents language/context-specific translations for Exhibitions.
 *
 * @property string $id Primary key (UUID)
 * @property string $exhibition_id Foreign key to exhibitions table
 * @property string $language_id Foreign key to languages table (ISO 639-1)
 * @property string $context_id Foreign key to contexts table
 * @property string $title
 * @property string $description
 * @property string|null $url
 * @property string|null $backward_compatibility
 * @property array|null $extra
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ExhibitionTranslation extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'exhibition_translations';

    protected $fillable = [
        'exhibition_id',
        'language_id',
        'context_id',
        'title',
        'description',
        'url',
        'backward_compatibility',
        'extra',
    ];

    protected $casts = [
        'extra' => 'array',
    ];

    public function uniqueIds(): array
    {
        return ['id'];
    }

    public function exhibition(): BelongsTo
    {
        return $this->belongsTo(Exhibition::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    public function context(): BelongsTo
    {
        return $this->belongsTo(Context::class);
    }

    /**
     * Scope a query to only include translations for the default context.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDefaultContext($query)
    {
        return $query->whereHas('context', function ($query) {
            $query->where('is_default', true);
        });
    }

    /**
     * Scope a query to only include translations for a specific language.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $languageId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForLanguage($query, $languageId)
    {
        return $query->where('language_id', $languageId);
    }

    /**
     * Scope a query to only include translations for a specific context.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $contextId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForContext($query, $contextId)
    {
        return $query->where('context_id', $contextId);
    }
}

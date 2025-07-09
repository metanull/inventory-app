<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ThemeTranslation Model
 *
 * Represents language/context-specific translations for Themes.
 *
 * @property string $id Primary key (UUID)
 * @property string $theme_id Foreign key to themes table
 * @property string $language_id Foreign key to languages table (ISO 639-1)
 * @property string $context_id Foreign key to contexts table
 * @property string $title
 * @property string $description
 * @property string $introduction
 * @property string|null $backward_compatibility
 * @property array|null $extra
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ThemeTranslation extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'theme_translations';

    protected $fillable = [
        'theme_id',
        'language_id',
        'context_id',
        'title',
        'description',
        'introduction',
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

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
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

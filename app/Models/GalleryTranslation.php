<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * GalleryTranslation Model
 *
 * Represents language and context-specific translations for Galleries.
 * Supports internationalization and contextualization of Gallery content.
 */
class GalleryTranslation extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'gallery_translations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'gallery_id',
        'language_id',
        'context_id',
        'title',
        'description',
        'url',
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
     * Get the gallery that owns the translation.
     */
    public function gallery(): BelongsTo
    {
        return $this->belongsTo(Gallery::class);
    }

    /**
     * Get the language of the translation.
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * Get the context of the translation.
     */
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

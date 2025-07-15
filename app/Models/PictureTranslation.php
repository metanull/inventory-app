<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PictureTranslation Model
 *
 * Represents language and context-specific translations for Pictures.
 * Supports internationalization and contextualization of Picture content.
 */
class PictureTranslation extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'picture_translations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'picture_id',
        'language_id',
        'context_id',
        'description',
        'caption',
        'author_id',
        'text_copy_editor_id',
        'translator_id',
        'translation_copy_editor_id',
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
     * Get the picture that owns the translation.
     */
    public function picture(): BelongsTo
    {
        return $this->belongsTo(Picture::class);
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
     * Get the author of the translation.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'author_id');
    }

    /**
     * Get the text copy editor.
     */
    public function textCopyEditor(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'text_copy_editor_id');
    }

    /**
     * Get the translator.
     */
    public function translator(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'translator_id');
    }

    /**
     * Get the translation copy editor.
     */
    public function translationCopyEditor(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'translation_copy_editor_id');
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

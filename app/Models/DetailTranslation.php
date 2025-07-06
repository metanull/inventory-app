<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * DetailTranslation Model
 *
 * Represents language and context-specific translations for Details.
 * Supports internationalization and contextualization of Detail content.
 *
 * @property string $id Primary key (UUID)
 * @property string $detail_id Foreign key to details table
 * @property string $language_id Foreign key to languages table (ISO 639-1)
 * @property string $context_id Foreign key to contexts table
 * @property string $name Main name/title in the given language
 * @property string|null $alternate_name Alternate name/title in the given language
 * @property string $description Main description in the given language
 * @property string|null $author_id Author of the translation
 * @property string|null $text_copy_editor_id Copy editor for the original text
 * @property string|null $translator_id Translator
 * @property string|null $translation_copy_editor_id Copy editor for the translation
 * @property string|null $backward_compatibility Legacy ID for migration/compatibility
 * @property array|null $extra Additional arbitrary data (JSON)
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class DetailTranslation extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'detail_translations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'detail_id',
        'language_id',
        'context_id',
        'name',
        'alternate_name',
        'description',
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
        'extra' => 'array',
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
     * Get the detail that owns the translation.
     */
    public function detail(): BelongsTo
    {
        return $this->belongsTo(Detail::class);
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

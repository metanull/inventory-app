<?php

namespace App\Models;

use Database\Factories\GlossaryTranslationFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * GlossaryTranslation Model
 *
 * Represents a language-specific translation/definition for a Glossary entry.
 */
class GlossaryTranslation extends Model
{
    /** @use HasFactory<GlossaryTranslationFactory> */
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'glossary_translations';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'glossary_id',
        'language_id',
        'definition',
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
     * Get the glossary that owns this translation.
     *
     * @return BelongsTo<Glossary, $this>
     */
    public function glossary(): BelongsTo
    {
        return $this->belongsTo(Glossary::class);
    }

    /**
     * Get the language of this translation.
     *
     * @return BelongsTo<Language, $this>
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * Scope a query to only include translations for a specific language.
     *
     * @param  Builder<static>  $query
     * @return Builder<static>
     */
    public function scopeForLanguage(Builder $query, string $languageId): Builder
    {
        return $query->where('language_id', $languageId);
    }
}

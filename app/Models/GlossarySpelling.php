<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * GlossarySpelling Model
 *
 * Represents a specific spelling/variation of a Glossary entry in a particular language.
 */
class GlossarySpelling extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'glossary_spellings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'glossary_id',
        'language_id',
        'spelling',
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
     * Get the glossary that owns this spelling.
     */
    public function glossary(): BelongsTo
    {
        return $this->belongsTo(Glossary::class);
    }

    /**
     * Get the language of this spelling.
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * Get the item translations linked to this spelling.
     */
    public function itemTranslations(): BelongsToMany
    {
        return $this->belongsToMany(ItemTranslation::class, 'item_translation_spelling', 'spelling_id', 'item_translation_id')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include spellings for a specific language.
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
     * Scope a query to search for a specific spelling.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $spelling
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForSpelling($query, $spelling)
    {
        return $query->where('spelling', $spelling);
    }
}

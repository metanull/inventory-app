<?php

namespace App\Models;

use App\Events\SpellingSaved;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

/**
 * GlossarySpelling Model
 *
 * Represents a specific spelling/variation of a Glossary entry in a particular language.
 */
class GlossarySpelling extends Model
{
    use HasFactory, HasUuids;

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saved(function ($spelling) {
            event(new SpellingSaved($spelling));
        });
    }

    /**
     * Delete the model from the database.
     * Ensures atomic deletion of the GlossarySpelling and its ItemTranslation links.
     *
     * @return bool|null
     *
     * @throws \LogicException
     */
    public function delete()
    {
        if (is_null($this->getKeyName())) {
            throw new \LogicException('No primary key defined on model.');
        }

        // If the model doesn't exist, nothing to delete
        if (! $this->exists) {
            return false;
        }

        // Fire the deleting event (for other listeners)
        if ($this->fireModelEvent('deleting') === false) {
            return false;
        }

        // Perform atomic deletion: detach item translations AND delete the model in one transaction
        return DB::transaction(function () {
            // First, detach all item translation links
            $this->itemTranslations()->detach();

            // Then perform the actual deletion
            $this->performDeleteOnModel();

            // Mark the model as non-existing
            $this->exists = false;

            // Fire the deleted event
            $this->fireModelEvent('deleted', false);

            return true;
        });
    }

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

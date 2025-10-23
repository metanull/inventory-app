<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Glossary Model
 *
 * Represents a specialized word or term used in the inventory system.
 * Each glossary entry can have multiple translations, spellings, and synonyms.
 */
class Glossary extends Model
{
    use HasFactory, HasUuids;

    /**
     * Delete the model from the database.
     * Ensures atomic deletion of the Glossary, its translations, spellings,
     * spelling links to ItemTranslations, and synonym relationships.
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

        // Fire the deleting event
        if ($this->fireModelEvent('deleting') === false) {
            return false;
        }

        // Perform atomic deletion in a transaction
        return DB::transaction(function () {
            // 1. For each spelling, detach all ItemTranslation links
            foreach ($this->spellings as $spelling) {
                $spelling->itemTranslations()->detach();
            }

            // 2. Delete all spellings
            $this->spellings()->delete();

            // 3. Delete all translations
            $this->translations()->delete();

            // 4. Detach all synonym relationships (both directions)
            $this->synonyms()->detach();
            $this->reverseSynonyms()->detach();

            // 5. Finally, delete the glossary itself
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
    protected $table = 'glossaries';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'internal_name',
        'backward_compatibility',
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
     * Get the translations for this glossary entry.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(GlossaryTranslation::class);
    }

    /**
     * Get the spellings for this glossary entry.
     */
    public function spellings(): HasMany
    {
        return $this->hasMany(GlossarySpelling::class);
    }

    /**
     * Get the synonyms for this glossary entry.
     */
    public function synonyms(): BelongsToMany
    {
        return $this->belongsToMany(Glossary::class, 'glossary_synonyms', 'glossary_id', 'synonym_id')
            ->withTimestamps();
    }

    /**
     * Get the reverse synonyms for this glossary entry.
     */
    public function reverseSynonyms(): BelongsToMany
    {
        return $this->belongsToMany(Glossary::class, 'glossary_synonyms', 'synonym_id', 'glossary_id')
            ->withTimestamps();
    }
}

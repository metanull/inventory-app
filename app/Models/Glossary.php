<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

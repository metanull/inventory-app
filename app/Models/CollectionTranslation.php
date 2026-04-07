<?php

namespace App\Models;

use App\Events\CollectionTranslationSaved;
use App\Traits\HasJsonFields;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

/**
 * CollectionTranslation Model
 *
 * Represents language and context-specific translations for Collections.
 * Supports internationalization and contextualization of Collection content.
 */
class CollectionTranslation extends Model
{
    use HasFactory, HasJsonFields, HasUuids;

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saved(function ($collectionTranslation) {
            event(new CollectionTranslationSaved($collectionTranslation));
        });
    }

    /**
     * Delete the model from the database.
     * Ensures atomic deletion of the CollectionTranslation and its spelling links.
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

        // Fire the deleting event (for other listeners, not for spelling detachment)
        if ($this->fireModelEvent('deleting') === false) {
            return false;
        }

        // Perform atomic deletion: detach spellings AND delete the model in one transaction
        return DB::transaction(function () {
            // First, detach all spelling links
            $this->spellings()->detach();

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
    protected $table = 'collection_translations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'collection_id',
        'language_id',
        'context_id',
        'title',
        'description',
        'quote',
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
     * Get the extra field decoded as an associative array.
     */
    protected function extraDecoded(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->normalizedJson('extra')
        );
    }

    /**
     * Get the collection that owns the translation.
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
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
     * Get the glossary spellings linked to this collection translation.
     */
    public function spellings(): BelongsToMany
    {
        return $this->belongsToMany(GlossarySpelling::class, 'collection_translation_spelling', 'collection_translation_id', 'spelling_id')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include translations for the default context.
     *
     * @param  Builder  $query
     * @return Builder
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
     * @param  Builder  $query
     * @param  string  $languageId
     * @return Builder
     */
    public function scopeForLanguage($query, $languageId)
    {
        return $query->where('language_id', $languageId);
    }

    /**
     * Scope a query to only include translations for a specific context.
     *
     * @param  Builder  $query
     * @param  string  $contextId
     * @return Builder
     */
    public function scopeForContext($query, $contextId)
    {
        return $query->where('context_id', $contextId);
    }
}

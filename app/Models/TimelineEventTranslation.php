<?php

namespace App\Models;

use App\Events\TimelineEventTranslationSaved;
use App\Traits\HasJsonFields;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class TimelineEventTranslation extends Model
{
    use HasFactory, HasJsonFields, HasUuids;

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::saved(function ($timelineEventTranslation) {
            event(new TimelineEventTranslationSaved($timelineEventTranslation));
        });
    }

    /**
     * Delete the model from the database.
     * Ensures atomic deletion of the TimelineEventTranslation and its spelling links.
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

    protected $table = 'timeline_event_translations';

    protected $fillable = [
        'timeline_event_id',
        'language_id',
        'name',
        'description',
        'date_from_description',
        'date_to_description',
        'date_from_ah_description',
        'backward_compatibility',
        'extra',
    ];

    protected $casts = [
        'extra' => 'object',
    ];

    /**
     * Get the columns that should automatically receive a unique identifier.
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
     * Get the timeline event that owns the translation.
     */
    public function timelineEvent(): BelongsTo
    {
        return $this->belongsTo(TimelineEvent::class);
    }

    /**
     * Get the language of the translation.
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * Get the glossary spellings linked to this timeline event translation.
     */
    public function spellings(): BelongsToMany
    {
        return $this->belongsToMany(GlossarySpelling::class, 'timeline_event_translation_spelling', 'timeline_event_translation_id', 'spelling_id')
            ->withTimestamps();
    }

    /**
     * Scope a query to only include translations for a specific language.
     */
    public function scopeForLanguage(Builder $query, string $languageId): Builder
    {
        return $query->where('language_id', $languageId);
    }
}

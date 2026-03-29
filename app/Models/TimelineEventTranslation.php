<?php

namespace App\Models;

use App\Traits\HasJsonFields;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimelineEventTranslation extends Model
{
    use HasFactory, HasJsonFields, HasUuids;

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
     * Scope a query to only include translations for a specific language.
     */
    public function scopeForLanguage(Builder $query, string $languageId): Builder
    {
        return $query->where('language_id', $languageId);
    }
}

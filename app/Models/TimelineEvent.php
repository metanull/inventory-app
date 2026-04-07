<?php

namespace App\Models;

use App\Traits\HasDisplayOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimelineEvent extends Model
{
    use HasDisplayOrder;
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'timeline_id',
        'internal_name',
        'year_from',
        'year_to',
        'year_from_ah',
        'year_to_ah',
        'date_from',
        'date_to',
        'display_order',
        'backward_compatibility',
        'extra',
    ];

    protected $casts = [
        'year_from' => 'integer',
        'year_to' => 'integer',
        'year_from_ah' => 'integer',
        'year_to_ah' => 'integer',
        'date_from' => 'date',
        'date_to' => 'date',
        'display_order' => 'integer',
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
     * Get a query builder scoped to this event's siblings (same timeline_id).
     *
     * @return Builder<static>
     */
    protected function getSiblingsQuery(): Builder
    {
        return static::where('timeline_id', $this->timeline_id);
    }

    /**
     * Get the timeline that owns this event.
     */
    public function timeline(): BelongsTo
    {
        return $this->belongsTo(Timeline::class);
    }

    /**
     * Get the translations for this event.
     */
    public function translations(): HasMany
    {
        return $this->hasMany(TimelineEventTranslation::class);
    }

    /**
     * Get the images for this event.
     */
    public function images(): HasMany
    {
        return $this->hasMany(TimelineEventImage::class)->orderBy('display_order');
    }

    /**
     * Get the items associated with this event.
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'timeline_event_item')
            ->withPivot('display_order', 'backward_compatibility', 'extra')
            ->withTimestamps();
    }
}

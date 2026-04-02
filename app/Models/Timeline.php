<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Timeline extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'internal_name',
        'country_id',
        'collection_id',
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
     * Get the country for this timeline.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the collection associated with this timeline.
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * Get the events for this timeline.
     */
    public function events(): HasMany
    {
        return $this->hasMany(TimelineEvent::class)->orderBy('display_order');
    }
}

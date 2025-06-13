<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partner extends Model
{
    use HasUuids;

    protected $with = [
        'country',
    ];

    protected $fillable = [
        // 'id',
        'internal_name',
        'type',
        'backward_compatibility',
        'country_id',
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
     * Get the items belonging to this partner.
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class)->chaperone();
    }

    /**
     * The country of the Item.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Detail extends Model
{
    /** @use HasFactory<\Database\Factories\DetailFactory> */
    use HasFactory;

    use HasUuids;

    protected $with = [
        'item',
    ];

    protected $fillable = [
        // 'id',
        'item_id',
        'internal_name',
        'backward_compatibility',
    ];

    /**
     * The item associated with the Detail.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get all contextualizations for this detail.
     */
    public function contextualizations(): HasMany
    {
        return $this->hasMany(Contextualization::class);
    }

    /**
     * Get the columns that should automatically receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['id'];
    }
}

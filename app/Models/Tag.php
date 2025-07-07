<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'internal_name',
        'backward_compatibility',
        'description',
    ];

    /**
     * The items that belong to this tag.
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class)->withTimestamps();
    }

    /**
     * Scope to get tags for a specific item.
     *
     * @param  string|Item  $item  The item ID or Item model instance
     */
    public function scopeForItem(Builder $query, $item): Builder
    {
        $itemId = $item instanceof Item ? $item->id : $item;

        return $query->whereHas('items', function (Builder $query) use ($itemId) {
            $query->where('items.id', $itemId);
        });
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

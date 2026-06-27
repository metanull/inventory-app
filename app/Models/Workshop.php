<?php

namespace App\Models;

use Database\Factories\WorkshopFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Workshop Model
 *
 * Represents workshops where items were created.
 * Workshops can be associated with multiple items through a many-to-many relationship.
 */
class Workshop extends Model
{
    /** @use HasFactory<WorkshopFactory> */
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'internal_name',
        'backward_compatibility',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the columns that should receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['id'];
    }

    /**
     * Items created by this workshop
     *
     * @return BelongsToMany<Item, $this>
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'item_workshop');
    }

    /**
     * Accessor to ensure null values instead of empty strings
     */
    public function getBackwardCompatibilityAttribute(?string $value): ?string
    {
        return $value === '' ? null : $value;
    }

    /**
     * Mutator to ensure null values instead of empty strings
     */
    public function setBackwardCompatibilityAttribute(mixed $value): void
    {
        $this->attributes['backward_compatibility'] = $value === '' ? null : $value;
    }
}

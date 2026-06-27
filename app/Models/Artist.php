<?php

namespace App\Models;

use Database\Factories\ArtistFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Artist Model
 *
 * Represents artists who have created items in the collection.
 * Artists can be associated with multiple items through a many-to-many relationship.
 */
class Artist extends Model
{
    /** @use HasFactory<ArtistFactory> */
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'place_of_birth',
        'place_of_death',
        'date_of_birth',
        'date_of_death',
        'period_of_activity',
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
     * Items created by this artist
     *
     * @return BelongsToMany<Item, $this>
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'artist_item');
    }

    // Accessors and Mutators to ensure null values instead of empty strings
    public function getPlaceOfBirthAttribute(?string $value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function setPlaceOfBirthAttribute(mixed $value): void
    {
        $this->attributes['place_of_birth'] = $value === '' ? null : $value;
    }

    public function getPlaceOfDeathAttribute(?string $value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function setPlaceOfDeathAttribute(mixed $value): void
    {
        $this->attributes['place_of_death'] = $value === '' ? null : $value;
    }

    public function getDateOfBirthAttribute(?string $value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function setDateOfBirthAttribute(mixed $value): void
    {
        $this->attributes['date_of_birth'] = $value === '' ? null : $value;
    }

    public function getDateOfDeathAttribute(?string $value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function setDateOfDeathAttribute(mixed $value): void
    {
        $this->attributes['date_of_death'] = $value === '' ? null : $value;
    }

    public function getPeriodOfActivityAttribute(?string $value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function setPeriodOfActivityAttribute(mixed $value): void
    {
        $this->attributes['period_of_activity'] = $value === '' ? null : $value;
    }

    public function getBackwardCompatibilityAttribute(?string $value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function setBackwardCompatibilityAttribute(mixed $value): void
    {
        $this->attributes['backward_compatibility'] = $value === '' ? null : $value;
    }
}

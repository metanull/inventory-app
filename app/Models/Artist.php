<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Artist Model
 *
 * Represents artists who have created items in the collection.
 * Artists can be associated with multiple items through a many-to-many relationship.
 *
 * @property string $id Primary key (UUID)
 * @property string $name Artist's name
 * @property string|null $place_of_birth Where the artist was born
 * @property string|null $place_of_death Where the artist died
 * @property string|null $date_of_birth When the artist was born
 * @property string|null $date_of_death When the artist died
 * @property string|null $period_of_activity When the artist was active
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string|null $backward_compatibility Legacy system reference
 * @property string $internal_name Internal identifier
 */
class Artist extends Model
{
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
     */
    public function uniqueIds(): array
    {
        return ['id'];
    }

    /**
     * Items created by this artist
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'artist_item');
    }

    // Accessors and Mutators to ensure null values instead of empty strings
    public function getPlaceOfBirthAttribute($value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function setPlaceOfBirthAttribute($value): void
    {
        $this->attributes['place_of_birth'] = $value === '' ? null : $value;
    }

    public function getPlaceOfDeathAttribute($value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function setPlaceOfDeathAttribute($value): void
    {
        $this->attributes['place_of_death'] = $value === '' ? null : $value;
    }

    public function getDateOfBirthAttribute($value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function setDateOfBirthAttribute($value): void
    {
        $this->attributes['date_of_birth'] = $value === '' ? null : $value;
    }

    public function getDateOfDeathAttribute($value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function setDateOfDeathAttribute($value): void
    {
        $this->attributes['date_of_death'] = $value === '' ? null : $value;
    }

    public function getPeriodOfActivityAttribute($value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function setPeriodOfActivityAttribute($value): void
    {
        $this->attributes['period_of_activity'] = $value === '' ? null : $value;
    }

    public function getBackwardCompatibilityAttribute($value): ?string
    {
        return $value === '' ? null : $value;
    }

    public function setBackwardCompatibilityAttribute($value): void
    {
        $this->attributes['backward_compatibility'] = $value === '' ? null : $value;
    }
}

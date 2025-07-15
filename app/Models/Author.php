<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Author Model
 *
 * Represents authors who write text content in the system.
 */
class Author extends Model
{
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
     */
    public function uniqueIds(): array
    {
        return ['id'];
    }

    /**
     * Accessor to ensure null values instead of empty strings
     */
    public function getInternalNameAttribute($value): ?string
    {
        return $value === '' ? null : $value;
    }

    /**
     * Mutator to ensure null values instead of empty strings
     */
    public function setInternalNameAttribute($value): void
    {
        $this->attributes['internal_name'] = $value === '' ? null : $value;
    }

    /**
     * Accessor to ensure null values instead of empty strings
     */
    public function getBackwardCompatibilityAttribute($value): ?string
    {
        return $value === '' ? null : $value;
    }

    /**
     * Mutator to ensure null values instead of empty strings
     */
    public function setBackwardCompatibilityAttribute($value): void
    {
        $this->attributes['backward_compatibility'] = $value === '' ? null : $value;
    }
}

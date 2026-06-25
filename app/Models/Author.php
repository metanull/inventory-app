<?php

namespace App\Models;

use Database\Factories\AuthorFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Author Model
 *
 * Represents authors who write text content in the system.
 */
class Author extends Model
{
    /** @use HasFactory<AuthorFactory> */
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'firstname',
        'lastname',
        'givenname',
        'originalname',
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
     * Accessor to ensure null values instead of empty strings
     */
    public function getInternalNameAttribute(?string $value): ?string
    {
        return $value === '' ? null : $value;
    }

    /**
     * Mutator to ensure null values instead of empty strings
     */
    public function setInternalNameAttribute(mixed $value): void
    {
        $this->attributes['internal_name'] = $value === '' ? null : $value;
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

    /**
     * Get the translations for this author.
     *
     * @return HasMany<AuthorTranslation, $this>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(AuthorTranslation::class);
    }
}

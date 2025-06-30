<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Author Model
 *
 * Represents authors who write text content in the system.
 * Authors can be assigned to different roles in internationalization records.
 *
 * @property string $id Primary key (UUID)
 * @property string $name Author's name (unique)
 * @property string|null $internal_name Internal name for multilingual support
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string|null $backward_compatibility Legacy system reference
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
     * Internationalization records where this author is the original author
     */
    public function authoredInternationalizations(): HasMany
    {
        return $this->hasMany(Internationalization::class, 'author_id');
    }

    /**
     * Internationalization records where this author is the text copy editor
     */
    public function textCopyEditedInternationalizations(): HasMany
    {
        return $this->hasMany(Internationalization::class, 'text_copy_editor_id');
    }

    /**
     * Internationalization records where this author is the translator
     */
    public function translatedInternationalizations(): HasMany
    {
        return $this->hasMany(Internationalization::class, 'translator_id');
    }

    /**
     * Internationalization records where this author is the translation copy editor
     */
    public function translationCopyEditedInternationalizations(): HasMany
    {
        return $this->hasMany(Internationalization::class, 'translation_copy_editor_id');
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Context extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        // 'id',
        'internal_name',
        'backward_compatibility',
        'is_default',
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
     * Get all contextualizations for this context.
     */
    public function contextualizations(): HasMany
    {
        return $this->hasMany(Contextualization::class);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    protected $casts = [
        'is_default' => 'boolean',
    ];

    // Add a method to mark a single row as the default
    public function setDefault()
    {
        // Ensure the table has a 'default' column (boolean or integer)
        if (! $this->exists) {
            throw new \Exception('Model instance does not exist.');
        }

        // Start a transaction to ensure atomicity
        return DB::transaction(function () {
            // Set all rows' 'default' column to false (or 0)
            self::query()->update(['is_default' => false]);

            // Set the current row's 'default' column to true (or 1)
            $this->update(['is_default' => true]);

            return $this;
        });
    }
}

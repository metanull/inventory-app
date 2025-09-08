<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    protected $casts = [
        'is_default' => 'boolean',
    ];

    // Mark a single row as the default
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

    // Unmark a single row as the default
    public function unsetDefault()
    {
        // Ensure the table has a 'default' column (boolean or integer)
        if (! $this->exists) {
            throw new \Exception('Model instance does not exist.');
        }

        // Start a transaction to ensure atomicity
        return DB::transaction(function () {
            // Set the current row's 'default' column to false (or 0)
            $this->update(['is_default' => false]);

            return $this;
        });
    }

    // Clear all defaults
    public static function clearDefault()
    {
        return DB::transaction(function () {
            // Set all rows' 'default' column to false (or 0)
            self::query()->update(['is_default' => false]);
        });
    }
}

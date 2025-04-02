<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

class Context extends Model
{
    use HasUuids;

    protected $fillable = [
        // 'id',
        'internal_name',
        'backward_compatibility',
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
    public function setInternalNameAttribute($value)
    {
        $this->attributes['internal_name'] = strtolower($value);
    }
    public function setBackwardCompatibilityAttribute($value)
    {
        $this->attributes['backward_compatibility'] = strtoupper($value);
    }

}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Project extends Model
{
    /** @use HasFactory<\Database\Factories\ProjectFactory> */
    use HasFactory;

    use HasUuids;

    protected $fillable = [
        // 'id',
        'internal_name',
        'backward_compatibility',
        'launch_date',
        'is_launched',
        'is_enabled',
        'primary_context_id',
        'primary_language_id',
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
    public function setLaunchDateAttribute($value)
    {
        $this->attributes['launch_date'] = $value ? date('Y-m-d', strtotime($value)) : null;
    }
    public function setIsLaunchedAttribute($value)
    {
        $this->attributes['is_launched'] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
    public function setIsEnabledAttribute($value)
    {
        $this->attributes['is_enabled'] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
    public function setPrimaryContextIdAttribute($value)
    {
        $this->attributes['primary_context_id'] = $value ? (string) $value : null;
    }
    public function setPrimaryLanguageIdAttribute($value)
    {
        $this->attributes['primary_language_id'] = $value ? (string) $value : null;
    }
}

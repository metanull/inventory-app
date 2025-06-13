<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    use HasFactory;
    use HasUuids;

    protected $with = [
        'context',
        'language',
    ];

    protected $fillable = [
        // 'id',
        'internal_name',
        'backward_compatibility',
        'launch_date',
        'is_launched',
        'is_enabled',
        'context_id',
        'language_id',
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
     * The context associated with the Project.
     */
    public function context(): BelongsTo
    {
        return $this->belongsTo(Context::class, 'context_id');
    }

    /**
     * The language associated with the Project.
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'language_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'launch_date' => 'datetime:Y-m-d',
        'is_launched' => 'boolean',
        'is_enabled' => 'boolean',
    ];

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true)
            ->where('is_launched', true);
    }
}

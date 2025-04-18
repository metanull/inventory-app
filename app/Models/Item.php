<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    use HasUuids;

    protected $fillable = [
        // 'id',
        'partner_id',
        'internal_name',
        'type',
        'backward_compatibility',
        'country_id',
    ];

    /**
     * The partner owning or responsible for the Item.
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
    /**
     * The country of the Item.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the columns that should automatically receive a unique identifier.
     *
     * @return array<int, string>
     */
    public function uniqueIds(): array
    {
        return ['id'];
    }

}

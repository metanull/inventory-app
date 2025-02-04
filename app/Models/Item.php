<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Item extends Model
{
    protected $fillable = [
        'id',
        'internal_name',
        'type',
        'backward_compatibility',
    ];

    protected $casts = [
        'id' => 'string',
    ];

    /**
     * The parent partner.
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * The contexts this item belongs to.
     */
    public function contexts(): BelongsToMany
    {
        return $this->belongsToMany(Context::class)->using(ContextItem::class);
    }
}

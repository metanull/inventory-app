<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ContextItem extends Pivot
{
    protected $fillable = [
        'context_id',
        'item_id',
    ];

    protected $casts = [
        'context_id' => 'string',
        'item_id' => 'string',
    ];

    //
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class)->using(ContextItem::class);
    }

    public function contexts(): BelongsToMany
    {
        return $this->belongsToMany(Context::class)->using(ContextItem::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Context::class)->using(Language::class);
    }
}

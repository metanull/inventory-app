<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Context extends Model
{
    /**
     * The items found in this context.
     */
    public function items(): BelongsToMany
    {
        return $this->belongsToMany(Item::class)->using(ContextItem::class);
    }
}

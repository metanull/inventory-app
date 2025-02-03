<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Partner extends Model
{
    /**
     * Get the items belonging to this partner.
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    /**
     * The contexts this item belongs to.
     */
    public function contexts(): BelongsToMany
    {
        return $this->belongsToMany(Context::class)->using(ContextPartner::class);
    }
}

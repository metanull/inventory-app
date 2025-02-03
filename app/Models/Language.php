<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Language extends Model
{
    public $incrementing = false; // Disable auto-incrementing
    protected $keyType = 'string'; // Specify the key type as string

    /**
     * Get the contextualized items for this language
     */
    public function context_items(): HasMany
    {
        return $this->hasMany(ContextItem::class);
    }

    /**
     * Get the contextualized partners for this language
     */
    public function context_partners(): HasMany
    {
        return $this->hasMany(ContextPartner::class);
    }
}

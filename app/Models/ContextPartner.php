<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ContextPartner extends Pivot
{
    //
    public function partners(): BelongsToMany
    {
        return $this->belongsToMany(Partner::class)->using(ContextPartner::class);
    }

    public function contexts(): BelongsToMany
    {
        return $this->belongsToMany(Context::class)->using(ContextPartner::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Context::class)->using(Language::class);
    }
}

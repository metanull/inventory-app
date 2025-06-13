<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    public $incrementing = false; // Disable auto-incrementing

    protected $keyType = 'string'; // Specify the key type as string

    protected $fillable = [
        'id',
        'internal_name',
        'backward_compatibility',
    ];

    /**
     * Get the items belonging to this country.
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class)->chaperone();
    }

    /**
     * Get the partners belonging to this country.
     */
    public function partners(): HasMany
    {
        return $this->hasMany(Partner::class)->chaperone();
    }
}

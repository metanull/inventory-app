<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    public $incrementing = false; // Disable auto-incrementing
    protected $keyType = 'string'; // Specify the key type as string

    protected $fillable = [
        'id',
        'internal_name',
        'backward_compatibility',
    ];
}

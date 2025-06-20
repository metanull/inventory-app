<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailableImage extends Model
{
    /** @use HasFactory<\Database\Factories\AvailableImageFactory> */
    use HasFactory;

    use HasUuids;

    protected $fillable = [
        'path',
        'comment',
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
}

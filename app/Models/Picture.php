<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Picture extends Model
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'path',
        'internal_name',
        'backward_compatibility',
        'copyright_text',
        'copyright_url',
        'upload_name',
        'upload_extension',
        'upload_mime_type',
        'upload_size',
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

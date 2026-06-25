<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string|null $path
 * @property string|null $name
 * @property string|null $extension
 * @property string|null $mime_type
 * @property int|null $size
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class ImageUpload extends Model
{
    /** @use HasFactory<\Database\Factories\ImageUploadFactory> */
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'path',
        'name',
        'extension',
        'mime_type',
        'size',
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

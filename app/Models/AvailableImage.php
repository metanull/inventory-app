<?php

namespace App\Models;

use App\Contracts\StreamableImageFile;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string|null $path
 * @property string|null $original_name
 * @property string|null $mime_type
 * @property int|null $size
 * @property string|null $comment
 */
class AvailableImage extends Model implements StreamableImageFile
{
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'path',
        'original_name',
        'mime_type',
        'size',
        'comment',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'size' => 'integer',
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

    public function imageDisk(): string
    {
        return config('localstorage.available.images.disk');
    }

    public function imageStoragePath(): string
    {
        return trim(config('localstorage.available.images.directory'), '/').'/'.$this->path;
    }

    public function imageMimeType(): ?string
    {
        return $this->mime_type ?: null;
    }

    public function imageDownloadFilename(): string
    {
        return $this->original_name ?: basename($this->path);
    }
}

<?php

namespace App\Models;

use App\Contracts\StreamableImageFile;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailableImage extends Model implements StreamableImageFile
{
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
        return null;
    }

    public function imageDownloadFilename(): string
    {
        return basename($this->path);
    }
}

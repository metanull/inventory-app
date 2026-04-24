<?php

namespace App\Http\Responses\Image;

use App\Contracts\StreamableImageFile;
use App\Http\Responses\FileResponse;
use Illuminate\Contracts\Support\Responsable;

class DownloadImageResponse implements Responsable
{
    public function __construct(private readonly StreamableImageFile $image) {}

    public function toResponse($request)
    {
        return FileResponse::download(
            $this->image->imageDisk(),
            $this->image->imageStoragePath(),
            $this->image->imageDownloadFilename(),
            $this->image->imageMimeType(),
        )->toResponse($request);
    }
}

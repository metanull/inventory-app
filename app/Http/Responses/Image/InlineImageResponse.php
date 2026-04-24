<?php

namespace App\Http\Responses\Image;

use App\Contracts\StreamableImageFile;
use App\Http\Responses\FileResponse;
use Illuminate\Contracts\Support\Responsable;

class InlineImageResponse implements Responsable
{
    public function __construct(private readonly StreamableImageFile $image) {}

    public function toResponse($request)
    {
        return FileResponse::view(
            $this->image->imageDisk(),
            $this->image->imageStoragePath(),
            $this->image->imageMimeType(),
        )->toResponse($request);
    }
}

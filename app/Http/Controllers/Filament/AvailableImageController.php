<?php

namespace App\Http\Controllers\Filament;

use App\Http\Controllers\Controller;
use App\Http\Responses\Image\DownloadImageResponse;
use App\Http\Responses\Image\InlineImageResponse;
use App\Models\AvailableImage;

class AvailableImageController extends Controller
{
    public function view(AvailableImage $availableImage): InlineImageResponse
    {
        return new InlineImageResponse($availableImage);
    }

    public function download(AvailableImage $availableImage): DownloadImageResponse
    {
        return new DownloadImageResponse($availableImage);
    }
}

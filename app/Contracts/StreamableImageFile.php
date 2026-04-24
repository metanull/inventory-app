<?php

namespace App\Contracts;

interface StreamableImageFile
{
    /**
     * Storage disk name (e.g. 'public').
     */
    public function imageDisk(): string;

    /**
     * Path inside the disk, including the directory prefix (e.g. 'images/uuid.jpg').
     */
    public function imageStoragePath(): string;

    /**
     * MIME type, or null to let the response auto-detect via mime_content_type.
     */
    public function imageMimeType(): ?string;

    /**
     * Filename advertised in the Content-Disposition: attachment; filename=… header.
     */
    public function imageDownloadFilename(): string;
}

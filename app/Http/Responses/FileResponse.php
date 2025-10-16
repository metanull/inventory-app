<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Storage;

/**
 * Responsable wrapper for serving binary files (images, documents, etc.).
 * This provides a clean, reusable way to return binary file responses
 * while delegating to Laravel's native file response methods which use
 * Symfony's BinaryFileResponse under the hood.
 */
class FileResponse implements Responsable
{
    protected string $disk;

    protected string $path;

    protected ?string $mimeType;

    protected string $disposition;

    protected ?string $filename;

    /**
     * Create a new file response.
     *
     * @param  string  $disk  Storage disk name
     * @param  string  $path  Path to the file within the disk
     * @param  string|null  $mimeType  MIME type of the file (auto-detected if null)
     * @param  string  $disposition  'inline' for viewing, 'attachment' for downloading
     * @param  string|null  $filename  Custom filename for downloads (auto-detected if null)
     */
    public function __construct(
        string $disk,
        string $path,
        ?string $mimeType = null,
        string $disposition = 'inline',
        ?string $filename = null
    ) {
        $this->disk = $disk;
        $this->path = $path;
        $this->mimeType = $mimeType;
        $this->disposition = $disposition;
        $this->filename = $filename;
    }

    /**
     * Create an HTTP response that represents the object.
     * Returns Symfony's BinaryFileResponse via Laravel's response helpers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function toResponse($request)
    {
        if (! Storage::disk($this->disk)->exists($this->path)) {
            abort(404, 'File not found');
        }

        $fullPath = Storage::disk($this->disk)->path($this->path);

        if ($this->disposition === 'attachment') {
            // Use response()->download() for attachment disposition
            $filename = $this->filename ?: basename($this->path);

            return response()->download($fullPath, $filename);
        } else {
            // Use response()->file() for inline disposition
            $mimeType = $this->mimeType ?: mime_content_type($fullPath);

            return response()->file($fullPath, [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'public, max-age=3600',
                'Content-Disposition' => 'inline',
            ]);
        }
    }

    /**
     * Create a response for viewing (inline disposition).
     */
    public static function view(string $disk, string $path, ?string $mimeType = null): self
    {
        return new self($disk, $path, $mimeType, 'inline');
    }

    /**
     * Create a response for downloading (attachment disposition).
     */
    public static function download(string $disk, string $path, ?string $filename = null, ?string $mimeType = null): self
    {
        return new self($disk, $path, $mimeType, 'attachment', $filename);
    }
}

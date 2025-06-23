<?php

namespace App\Listeners;

use App\Events\AvailableImageEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\WhitespacePathNormalizer;

/**
 * Class AvailableImageListener
 *
 * This listener handles the event when an image becomes available for further processing.
 * It is responsible for moving the image from the upload directory to the public directory
 * and updating the database record accordingly.
 *
 * It is triggered after an image is uploaded, checked and processed, ensuring that the image
 * is moved to the appropriate location for public access.
 */
class AvailableImageListener
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(AvailableImageEvent $event): void
    {
        $file = $event->availableImage;

        // Get disk and directory where uploaded images are stored
        $uploadDisk = config('localstorage.uploads.images.disk');
        $uploadeDir = trim(config('localstorage.uploads.images.directory'), '/');
        // Get disk and directory where public images are stored
        $finalDisk = config('localstorage.public.images.disk');
        $finalDir = trim(config('localstorage.public.images.directory'), '/');

        // Get filename
        $filename = basename($file->path);

        // Move the file from the source disk to the destination disk
        Storage::disk($finalDisk)->writeStream(
            $finalDir.'/'.$filename,
            Storage::disk($uploadeDisk)->readStream($uploadeDir.'/'.$filename)
        );
        // Delete the file from the source disk
        Storage::disk($uploadeDisk)->delete($uploadeDir.'/'.$filename);

        // Update the file model with the new path
        $normalizer = new WhitespacePathNormalizer;

        $file->path = $normalizer->normalizePath($finalDir.'/'.$filename);
        $file->save();
    }
}

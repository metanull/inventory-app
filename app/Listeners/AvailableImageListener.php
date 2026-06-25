<?php

namespace App\Listeners;

use App\Events\AvailableImageEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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

        if ($file->path === null) {
            Log::error('AvailableImage has no path.', ['id' => $file->id]);

            return;
        }

        // Get disk and directory where uploaded images are stored
        $uploadDisk = Config::string('localstorage.uploads.images.disk');
        $uploadeDir = trim(Config::string('localstorage.uploads.images.directory'), '/');
        // Get disk and directory where public images are stored
        $finalDisk = Config::string('localstorage.available.images.disk');
        $finalDir = trim(Config::string('localstorage.available.images.directory'), '/');

        // Get filename (path should already be just filename)
        $filename = basename($file->path);

        // Move the file from the source disk to the destination disk
        $readStream = Storage::disk($uploadDisk)->readStream($uploadeDir.'/'.$filename);
        if ($readStream === null) {
            Log::error('Failed to open read stream for available image.', ['filename' => $filename]);

            return;
        }
        Storage::disk($finalDisk)->writeStream($finalDir.'/'.$filename, $readStream);
        // Delete the file from the source disk
        Storage::disk($uploadDisk)->delete($uploadeDir.'/'.$filename);

        // Update the file model with just the filename (no directory)
        $file->path = $filename;
        $file->save();
    }
}

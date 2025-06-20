<?php

namespace App\Listeners;

use App\Events\AvailableImageEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;

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

        // Get disk and directory config
        $sourceDisk = config('localstorage.uploads.images.disk');
        $sourceDir = trim(config('localstorage.uploads.images.directory'), '/');
        $destDisk = config('localstorage.public.images.disk');
        $destDir = trim(config('localstorage.public.images.directory'), '/');

        // Get filename
        $filename = basename($file->path);

        // Move the file from the source disk to the destination disk
        Storage::disk($destDisk)->writeStream(
            $destDir.'/'.$filename,
            Storage::disk($sourceDisk)->readStream($sourceDir.'/'.$filename)
        );
        // Delete the file from the source disk
        Storage::disk($sourceDisk)->delete($sourceDir.'/'.$filename);

        // Update the file model with the new path
        $file->path = $destDir.'/'.$filename;
        $file->save();
    }
}

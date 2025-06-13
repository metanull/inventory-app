<?php

namespace App\Listeners;

use App\Events\PictureUploaded;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ProcessPictureUploaded
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
    public function handle(PictureUploaded $event): void
    {
        $file = $event->picture;
        $path = Storage::path($file->path);

        if (exif_imagetype($path)) {

            $manager = new ImageManager(
                new Driver
            );
            $picture = $manager->read($path);
            $picture->resize(1280, 720, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $encoded = $picture->toJpg();
            $encoded->save($path);
            // Update the file record with the resized path
            $file->update(['path' => $path]);

            /*$picture = Image::make($path)->fit(1280, 720);
            // $resizedPath = 'local/Pictures/' . $file->filename;
            $resizedPath = $path;
            $picture->save(Storage::path($resizedPath));


            // Update the file record with the resized path
            $file->update(['path' => $resizedPath]);
            */
        }
    }
}

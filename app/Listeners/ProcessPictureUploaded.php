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
            $targetWidth = 1280;
            $picture = $manager->read($path);
            $width = $picture->width();
            $height = $picture->height();
            $aspectRatio = $width / $height;
            $targetHeight = round($targetWidth / $aspectRatio);
            $picture->resize($targetWidth, $targetHeight);
            
            */
            if (!is_null($event->picture->copyright_text)) {
                $picture->text($event->picture->copyright_text, 800, 800, function ($font) {
                    // $font->file(public_path('fonts/your-font.ttf')); // Optional: Specify a custom font
                    $font->size(36); // Font size
                    $font->color('#00ff00'); // Font color
                    $font->align('center'); // Horizontal alignment
                    $font->valign('top'); // Vertical alignment
                    $font->angle(45); // Text rotation (optional)
                });
            }

            $encoded = $picture->toJpg();
            $encoded->save($path);
            //            \Log::info("Resized picture: {$file->path} to 1280x720");
            // Update the file record with the resized path
            $file->update(['path' => $path]);
        }
    }
}

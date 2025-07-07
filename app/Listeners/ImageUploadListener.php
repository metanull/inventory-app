<?php

namespace App\Listeners;

use App\Events\AvailableImageEvent;
use App\Events\ImageUploadEvent;
use App\Models\AvailableImage;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use League\Flysystem\WhitespacePathNormalizer;

/**
 * Class ImageUploadListener
 *
 * This listener handles the image upload event, resizing images if necessary
 * and dispatching an event when the image is ready (available for further processing).
 *
 * It is dispatched when an image is uploaded, and stored in the private 'local' storage
 * and it checks if the uploaded file is a valid image.
 * If the image is valid, it resizes it to fit within the specified maximum dimensions
 * while preserving the aspect ratio, and it fires an AvailableImageEvent event.
 * If the image is not valid, it deletes the file.
 */
class ImageUploadListener
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
    public function handle(ImageUploadEvent $event): void
    {
        $file = $event->imageUpload;

        // Get disk and directory config for the uploaded images
        $uploadDisk = config('localstorage.uploads.images.disk');

        $path = Storage::disk($uploadDisk)->path($file->path);

        $normalizer = new WhitespacePathNormalizer;
        $path = $normalizer->normalizePath($path);

        if (exif_imagetype($path)) {
            $manager = new ImageManager(
                new Driver
            );
            $targetWidth = config('localstorage.public.images.max_width');
            $targetHeight = config('localstorage.public.images.max_height');

            $imageUpload = $manager->read($path);
            $width = $imageUpload->width();
            $height = $imageUpload->height();

            // If the image is bigger than the target size, resize it (preserving the original aspect ratio)
            // Calculate the new dimensions while preserving the aspect ratio
            $aspectRatio = $width / $height;
            $doResize = true;
            if ($width > $targetWidth && $height > $targetHeight) {
                if ($width / $height > $targetWidth / $targetHeight) {
                    $targetHeight = round($targetWidth / $aspectRatio);
                } else {
                    $targetWidth = round($targetHeight * $aspectRatio);
                }
            } elseif ($width > $targetWidth) {
                $targetHeight = round($targetWidth / $aspectRatio);
            } elseif ($height > $targetHeight) {
                $targetWidth = round($targetHeight * $aspectRatio);
            } else {
                $doResize = false;
            }
            if ($doResize) {
                $imageUpload->resize($targetWidth, $targetHeight);

                // Save the resized image back to the same path, type and quality
                $imageUpload->encode();
                $imageUpload->save($path);
            }

            $availableImage = new AvailableImage(['path' => $event->imageUpload->path]);
            $availableImage->id = $event->imageUpload->id;
            $availableImage->save();
            AvailableImageEvent::dispatch($availableImage);
        } else {
            // If the file is not an image, delete it
            Storage::disk($uploadDisk)->delete($file->path);

            // Optionally, you can log an error or throw an exception
            Log::error('Uploaded file was not a valid image.', [
                'disk' => $uploadDisk,
                'name' => $file->name,
                'path' => $file->path,
            ]);
        }
    }
}

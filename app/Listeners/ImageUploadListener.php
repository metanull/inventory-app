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

        // Check if the file exists on the upload disk
        if (! Storage::disk($uploadDisk)->exists($file->path)) {
            Log::error('Uploaded file does not exist.', [
                'disk' => $uploadDisk,
                'name' => $file->name,
                'path' => $file->path,
            ]);

            return;
        }

        // Get the file contents for image validation and processing
        $fileContents = Storage::disk($uploadDisk)->get($file->path);

        // Check if contents were successfully retrieved
        if ($fileContents === null) {
            Log::error('Failed to retrieve file contents.', [
                'disk' => $uploadDisk,
                'name' => $file->name,
                'path' => $file->path,
            ]);

            return;
        }

        try {
            // Use Intervention Image Manager to read and validate the image
            $manager = new ImageManager(new Driver);
            $image = $manager->read($fileContents);

            // If we get here, the image is valid
            $targetWidth = config('localstorage.available.images.max_width');
            $targetHeight = config('localstorage.available.images.max_height');

            $width = $image->width();
            $height = $image->height();

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
                $image->resize($targetWidth, $targetHeight);
                // Get the processed image data
                $processedImageData = $image->encode()->toString();
            } else {
                // Use the original file contents if no resize needed
                $processedImageData = $fileContents;
            }

            // Move the file from uploads to available images directory
            $availableImageDisk = config('localstorage.available.images.disk');
            $availableImageDirectory = config('localstorage.available.images.directory');

            // Get just the filename (without directory)
            $filename = basename($file->path);

            // Store file in configured directory on disk
            $storagePath = $availableImageDirectory.'/'.$filename;
            $putResult = Storage::disk($availableImageDisk)->put($storagePath, $processedImageData);

            if (! $putResult) {
                Log::error('Failed to store processed image.', [
                    'disk' => $availableImageDisk,
                    'path' => $storagePath,
                    'data_size' => strlen($processedImageData),
                ]);

                return;
            }

            // Clean up the original upload file
            Storage::disk($uploadDisk)->delete($file->path);

            // Store only filename in database (no directory)
            $availableImage = new AvailableImage(['path' => $filename]);
            $availableImage->id = $event->imageUpload->id;
            $availableImage->save();

            // Delete the ImageUpload record after successful processing
            $event->imageUpload->delete();

            AvailableImageEvent::dispatch($availableImage);

        } catch (\Exception $e) {
            // If the file is not a valid image or processing failed, delete it
            Storage::disk($uploadDisk)->delete($file->path);

            Log::error('Failed to process uploaded image.', [
                'disk' => $uploadDisk,
                'name' => $file->name,
                'path' => $file->path,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

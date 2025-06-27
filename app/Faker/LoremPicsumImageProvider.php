<?php

namespace App\Faker;

use Faker\Provider\Base;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\WhitespacePathNormalizer;

/**
 * Class LoremPicsumImageProvider
 * Provides methods to generate and manipulate image URLs using the Lorem Picsum service.
 *
 * The purpose of this class is to replace the functionality of the original FakerPhp's image
 * formatter, as it was deprecated (see: https://fakerphp.org/formatters/image).
 *
 * It is intended to be used in Laravel applications where Faker is used for seeding databases with
 * test data, particularly for generating image URLs and downloading images for testing purposes.
 *
 * This class extends the Faker Base provider to add functionality for generating random image URLs
 * and downloading images from Lorem Picsum. It supports generating images with specific dimensions,
 * optional IDs, and seeds for randomization. It also provides methods to download images and store
 * them on a specified storage disk, handling the necessary path normalization and error handling.
 *
 * Warm thanks to accreditly.io and in particular to the author of the following article:
 * https://accreditly.io/articles/how-to-replace-laravels-faker-default-image-provider for the
 * detailed explanations on how to replace the default Faker image provider.
 *
 * Warm thanks to David Marby and Nijiko Yonskai, authors of Lorem Picsum for providing a free image
 * service that allows us to generate random images.
 *
 * @see https://accreditly.io/articles/how-to-replace-laravels-faker-default-image-provider for the tutorial on replacing the default Faker image provider.
 * @see https://picsum.photos/ for more information on the Picsum image service.
 * @see https://fakerphp.org/formatters/image/ for the original implementation.
 */
class LoremPicsumImageProvider extends Base
{
    /**
     * Generate a random image URL from Lorem Picsum.
     *
     * @param  int  $width  Width of the image
     * @param  int  $height  Height of the image
     * @param  int|null  $id  Optional image ID to fetch a specific image
     * @param  string|null  $seed  Optional seed for random image generation
     * @param  array  $options  Additional query parameters to append to the URL. See https://picsum.photos/ for available options.
     * @return string The generated image URL
     */
    public function imageUrl(int $width = 640, int $height = 480, ?int $id = null, ?string $seed = null, ?array $options = []): string
    {
        if ($id !== null) {
            $url = "https://picsum.photos/id/{$id}/{$width}/{$height}.jpg";
        } elseif ($seed !== null) {
            $url = "https://picsum.photos/seed/{$seed}/{$width}/{$height}.jpg";
        } else {
            $url = "https://picsum.photos/{$width}/{$height}.jpg";
        }
        $queryParams = array_merge(['random' => rand()], $options);
        if (! empty($queryParams)) {
            $url .= '?'.http_build_query($queryParams);
        }

        return $url;
    }

    /**
     * Download a remote image from Lorem Picsum and store it on the specified Storage disk.
     *
     * @param  int  $width  Width of the image
     * @param  int  $height  Height of the image
     * @param  string  $disk  Storage disk to save the image
     * @param  string|null  $directory  Directory to save the image in
     * @param  int|null  $id  Optional image ID to fetch a specific image
     * @param  string|null  $seed  Optional seed for random image generation
     * @param  array  $options  Additional query parameters to append to the URL. See https://picsum.photos/ for available options.
     * @return string The path where the image is stored (relative to the disk root)
     *
     * @throws \Illuminate\Http\Client\RequestException If the HTTP request to fetch the image fails
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException If the paths cannot be resolved
     */
    public function image(int $width = 640, int $height = 480, string $disk = 'local', ?string $directory = null, ?int $id = null, ?string $seed = null, ?array $options = []): string
    {
        $url = $this->imageUrl($width, $height, $id, $seed, $options);
        $imageContents = $this->getImageContent($url);

        return $this->storeImageContent($imageContents, $disk, $directory);
    }

    /**
     * Download a remote image and return its contents.
     *
     * @param  string  $url  The URL of the image to download
     * @return string The image contents
     *
     * @throws \Illuminate\Http\Client\RequestException If the HTTP request fails
     */
    protected function getImageContent(string $url): string
    {
        // In testing environment, return a fake image content instead of making real HTTP requests
        if (app()->environment('testing')) {
            // Generate a valid 1x1 PNG image for testing
            $image = imagecreate(1, 1);
            $white = imagecolorallocate($image, 255, 255, 255);
            imagefill($image, 0, 0, $white);
            
            ob_start();
            imagepng($image);
            $content = ob_get_clean();
            imagedestroy($image);
            
            return $content;
        }

        $response = Http::get($url);
        $response->throw();

        return $response->body();
    }

    /**
     * Store the image contents to the specified disk and directory.
     *
     * This method normalizes the path and ensures the directory exists before saving.
     * It will throw an exception if the storage operation fails.
     *
     * @param  string  $imageContents  The raw image data to store
     * @param  string  $disk  The storage disk to use (default: 'local')
     * @param  string|null  $directory  The directory to store the image in (optional)
     * @param  string|null  $filename  The name of the file to save (optional, generates a unique name if not provided)
     * @return string The normalized relative path of the stored image
     *
     * @throws \RuntimeException If the storage operation fails
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException If the paths cannot be resolved
     */
    protected function storeImageContent(string $imageContents, string $disk = 'local', ?string $directory = null, ?string $filename = null): string
    {
        $normalizer = new WhitespacePathNormalizer;
        if ($filename === null) {
            $filename = uniqid('picsum_', true).'.jpg';
        } else {
            $filename = $normalizer->normalizePath($filename);
        }
        $storagePath = $filename;

        if ($directory !== null) {
            $directory = $normalizer->normalizePath($directory);

            if (! Storage::disk($disk)->exists($directory)) {
                Storage::disk($disk)->makeDirectory($directory);
            }

            $storagePath = "{$directory}/{$filename}";
        }

        if (Storage::disk($disk)->put($storagePath, $imageContents) === false) {
            throw new \RuntimeException("Failed to save image to {$storagePath} on disk {$disk}");
        }

        return $this->normalizeRelativePath($disk, $storagePath);
    }

    /**
     * Normalize the relative path of the stored image.
     *
     * This method resolves the real paths of the base and storage paths,
     * and normalizes the relative path to ensure it is clean and consistent.
     *
     * @param  string  $disk  The storage disk to use
     * @param  string  $storage_path  The storage path of the image
     * @return string The normalized relative path
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException If the paths cannot be resolved
     */
    protected function normalizeRelativePath(string $disk, string $storage_path): string
    {
        $normalizer = new WhitespacePathNormalizer;
        $real_base_path = realpath(Storage::disk($disk)->path(''));
        $real_storage_path = realpath(Storage::disk($disk)->path($storage_path));

        if ($real_base_path === false || $real_storage_path === false) {
            throw new \Illuminate\Contracts\Filesystem\FileNotFoundException('Failed to resolve paths');
        }

        return $normalizer->normalizePath(substr($real_storage_path, strlen($real_base_path) + 1));
    }
}

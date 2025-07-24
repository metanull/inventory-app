<?php

namespace App\Faker;

use Faker\Provider\Base;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\WhitespacePathNormalizer;

/**
 * Class LocalImageProvider
 * Provides methods to generate images using local seed images for fast, reliable seeding.
 *
 * This provider replaces network-dependent image generation with local file copying,
 * dramatically improving seeding performance and eliminating network dependencies.
 *
 * The provider uses a pool of pre-downloaded images stored in database/seeders/data/images/
 * and randomly selects from them when creating test images. This ensures:
 * - Fast seeding (no network calls)
 * - Reliable seeding (no network failures)
 * - Consistent image sizes and quality
 * - Realistic test data
 */
class LocalImageProvider extends Base
{
    /**
     * Array of available local seed images
     */
    protected static array $seedImages = [
        'alan_haverty.jpg',
        'alejandro_escamilla.jpg',
        'aleks_dorohovich.jpg',
        'allyson_souza.jpg',
        'art_wave.jpg',
        'austin_neill.jpg',
        'christopher_sardegna_2.jpg',
        'christopher_sardegna.jpg',
        'how_soon_ngu.jpg',
        'ireneuilia.jpg',
        'j_duclos.jpg',
        'jeffrey_kam.jpg',
        'luke_chesser_2.jpg',
        'luke_chesser_3.jpg',
        'luke_chesser.jpg',
        'margaret_barley.jpg',
        'nicholas_swanson_2.jpg',
        'nicholas_swanson.jpg',
        'nithya_ramanujam.jpg',
        'oleg_chursin.jpg',
        'rodrigo_melo.jpg',
        'ryan_mcguire.jpg',
        'sebastian_muller.jpg',
        'shane_colella.jpg',
        'shyamanta_baruah.jpg',
        'tony_naccarato.jpg',
        'tyler_wanlass_2.jpg',
        'tyler_wanlass.jpg',
        'vadim_sherbakov.jpg',
    ];

    /**
     * Path to the seed images directory
     */
    protected static string $seedImagesPath = 'database/seeders/data/images';

    /**
     * Generate a random image URL pointing to a local seed image.
     *
     * @param  int  $width  Width of the image (ignored, all seed images are 640x480)
     * @param  int  $height  Height of the image (ignored, all seed images are 640x480)
     * @param  string|null  $seed  Optional seed for deterministic image selection
     * @return string The path to a local seed image
     */
    public function imageUrl(int $width = 640, int $height = 480, ?string $seed = null): string
    {
        if ($seed !== null) {
            // Use seed for deterministic selection
            $index = crc32($seed) % count(static::$seedImages);
        } else {
            $index = array_rand(static::$seedImages);
        }

        $filename = static::$seedImages[$index];

        return static::$seedImagesPath.'/'.$filename;
    }

    /**
     * Copy a local seed image to the specified Storage disk.
     *
     * @param  int  $width  Width of the image (ignored, all seed images are 640x480)
     * @param  int  $height  Height of the image (ignored, all seed images are 640x480)
     * @param  string  $disk  Storage disk to save the image
     * @param  string|null  $directory  Directory to save the image in
     * @param  string|null  $seed  Optional seed for deterministic image selection
     * @param  array  $options  Additional options (ignored for compatibility)
     * @return string The path where the image is stored (relative to the disk root)
     *
     * @throws \RuntimeException If the source image cannot be found or copied
     */
    public function image(int $width = 640, int $height = 480, string $disk = 'local', ?string $directory = null, ?string $seed = null, ?array $options = []): string
    {
        // Validate that seed images are available before proceeding
        if (! $this->validateSeedImages()) {
            throw new \RuntimeException(
                'Local seed images are not available. Please ensure the seed images directory exists at: '.
                base_path(static::$seedImagesPath).'. You may need to download seed images or use remote image provider.'
            );
        }

        // Select a random seed image
        if ($seed !== null) {
            $index = crc32($seed) % count(static::$seedImages);
        } else {
            $index = array_rand(static::$seedImages);
        }

        $sourceFilename = static::$seedImages[$index];
        $sourcePath = base_path(static::$seedImagesPath.'/'.$sourceFilename);

        // Verify source file exists
        if (! file_exists($sourcePath)) {
            throw new \RuntimeException("Seed image not found: {$sourcePath}");
        }

        // Read the image content
        $imageContents = file_get_contents($sourcePath);
        if ($imageContents === false) {
            throw new \RuntimeException("Failed to read seed image: {$sourcePath}");
        }

        return $this->storeImageContent($imageContents, $disk, $directory);
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
            $filename = uniqid('seed_', true).'.jpg';
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

    /**
     * Get a random seed image filename from the available pool
     *
     * @return string Random seed image filename
     */
    public function seedImageFilename(): string
    {
        return static::$seedImages[array_rand(static::$seedImages)];
    }

    /**
     * Get the full path to a specific seed image
     *
     * @param  string  $filename  The seed image filename
     * @return string Full path to the seed image
     */
    public function seedImagePath(string $filename): string
    {
        return base_path(static::$seedImagesPath.'/'.$filename);
    }

    /**
     * Check if all seed images are available
     *
     * @return bool True if all seed images exist
     */
    public function validateSeedImages(): bool
    {
        foreach (static::$seedImages as $filename) {
            $path = $this->seedImagePath($filename);
            if (! file_exists($path)) {
                return false;
            }
        }

        return true;
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ImageUpload>
 */
class ImageUploadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $disk = config('localstorage.uploads.images.disk');
        $directory = config('localstorage.uploads.images.directory');

        $image = $this->faker->image(disk: $disk, directory: $directory, options: ['grayscale' => true]);
        $image_name = basename($image);
        $image_directory = dirname($image);

        return [
            'path' => $image,
            'name' => $image_name,
            'extension' => 'jpg',
            'mime_type' => Storage::disk($disk)->mimeType($image),
            'size' => Storage::disk($disk)->size($image),
        ];

    }
}

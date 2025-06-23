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
        /*$image = $this->faker->image(disk: 'local', directory: 'image_uploads', options: ['grayscale' => true]);
        $image_name = basename($image);
        $image_directory = dirname($image);

        return [
            'path' => $image_directory,
            'name' => $image_name,
            'extension' => 'jpg',
            'mime_type' => Storage::disk('local')->mimeType($image),
            'size' => Storage::disk('local')->size($image),
        ];*/

        
        $disk = config('localstorage.uploads.images.disk');
        $directory = config('localstorage.uploads.images.directory');

        $image = $this->faker->image(disk: $disk, directory: $directory, options: ['grayscale' => true]);
        $image_name = basename($image);
        $image_directory = dirname($image);

        return [
            'path' => $image_directory,
            'name' => $image_name,
            'extension' => 'jpg',
            'mime_type' => Storage::disk($disk)->mimeType($image),
            'size' => Storage::disk($disk)->size($image),
        ];
        
    }
}

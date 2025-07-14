<?php

namespace Database\Factories;

use App\Models\Detail;
use App\Models\Item;
use App\Models\Partner;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Picture>
 */
class PictureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $disk = config('localstorage.pictures.disk');
        $directory = config('localstorage.pictures.directory');

        // Use our custom LoremPicsumImageProvider instead of the deprecated faker image method
        $imagePath = $this->faker->image(640, 480, $disk, $directory, null, null, ['grayscale' => true]);
        $filename = basename($imagePath);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        return [
            'internal_name' => $this->faker->unique()->words(3, true),
            'backward_compatibility' => $this->faker->optional()->bothify('???/???/???/##'),
            'copyright_text' => $this->faker->optional()->words(4, true),
            'copyright_url' => $this->faker->optional()->url(),
            'path' => $imagePath,
            'upload_name' => $filename,
            'upload_extension' => $extension,
            'upload_mime_type' => 'image/jpeg',
            'upload_size' => Storage::disk($disk)->size($imagePath),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        // Don't set a default polymorphic relationship
        // This allows the factory to create standalone pictures by default
        return $this;
    }

    /**
     * Configure the model factory to attach to an Item.
     */
    public function forItem(): static
    {
        return $this->for(Item::factory(), 'pictureable');
    }

    /**
     * Configure the model factory to attach to a Detail.
     */
    public function forDetail(): static
    {
        return $this->for(Detail::factory(), 'pictureable');
    }

    /**
     * Configure the model factory to attach to a Partner.
     */
    public function forPartner(): static
    {
        return $this->for(Partner::factory(), 'pictureable');
    }

    /**
     * Configure the model factory to create standalone pictures (no polymorphic relationship).
     * Useful for many-to-many relationships like themes.
     */
    public function standalone(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'pictureable_type' => null,
                'pictureable_id' => null,
            ];
        });
    }
}

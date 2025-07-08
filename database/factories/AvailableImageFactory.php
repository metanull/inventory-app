<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AvailableImage>
 */
class AvailableImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $disk = config('localstorage.available.images.disk');
        $directory = config('localstorage.available.images.directory');

        return [
            'path' => $this->faker->image(width: 640, height: 480, disk: $disk, directory: $directory, options: ['grayscale' => true]),
            'comment' => $this->faker->sentence(10),
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

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
        return [
            'path' => $this->faker->imageUrl(640, 480, 'nature', true, 'Faker', true),
            'name' => $this->faker->word().'.jpg',
            'extension' => 'jpg',
            'mime_type' => 'image/jpeg',
            'size' => $this->faker->numberBetween(1000, 5000000),
        ];
    }
}

<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

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
        return [
            'path' => $this->faker->imageUrl(640, 480, 'nature', true, 'Faker', true),
            'internal_name' => $this->faker->unique()->words(3, true),
            'backward_compatibility' => $this->faker->bothify('???/???/???/##'),
            'copyright_text' => $this->faker->words(4, true),
            'copyright_url' => $this->faker->url(),
            'upload_name' => $this->faker->word().'.jpg',
            'upload_extension' => 'jpg',
            'upload_mime_type' => 'image/jpeg',
            'upload_size' => $this->faker->numberBetween(1000, 5000000), // Size in bytes
        ];
    }
}

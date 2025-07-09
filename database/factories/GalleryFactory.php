<?php

namespace Database\Factories;

use App\Models\Gallery;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Gallery>
 */
class GalleryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Gallery::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'internal_name' => $this->faker->unique()->slug(2),
            'backward_compatibility' => null,
        ];
    }

    /**
     * Indicate that the gallery has backward compatibility data.
     */
    public function withBackwardCompatibility(): static
    {
        return $this->state(fn (array $attributes) => [
            'backward_compatibility' => $this->faker->uuid(),
        ]);
    }
}

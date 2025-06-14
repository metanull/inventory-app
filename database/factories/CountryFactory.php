<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Country>
 */
class CountryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->unique()->countryCode(),
            'internal_name' => $this->faker->unique()->word(),
            'backward_compatibility' => $this->faker->optional()->word(),
            /*
            // Other syntax, which one is right?
            'id' => fake()->unique()->countryCode(),
            'internal_name' => fake()->unique()->name,
            'backward_compatibility' => fake()->optional()->word(),
            */
        ];
    }
}

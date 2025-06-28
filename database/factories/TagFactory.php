<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
 */
class TagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->unique()->uuid(),
            'internal_name' => $this->faker->unique()->words(3, true),
            'backward_compatibility' => $this->faker->lexify('???'),
            'description' => $this->faker->words(5, true),
        ];
    }
}

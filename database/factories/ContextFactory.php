<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Context>
 */
class ContextFactory extends Factory
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
            'internal_name' => $this->faker->unique()->word(),
            'backward_compatibility' => $this->faker->optional()->word(),
            'is_default' => false,
        ];
    }

    public function withIsDefault(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => true,
        ]);
    }
}

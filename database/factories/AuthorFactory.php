<?php

namespace Database\Factories;

use App\Models\Author;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Author>
 */
class AuthorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'internal_name' => $this->faker->optional(0.7)->firstName().' '.$this->faker->lastName(),
            'backward_compatibility' => $this->faker->optional(0.3)->uuid(),
        ];
    }
}

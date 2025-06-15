<?php

namespace Database\Factories;

use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Partner>
 */
class PartnerFactory extends Factory
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
            'backward_compatibility' => $this->faker->optional()->bothify('???_##'),
            'type' => $this->faker->randomElement(['museum', 'institution', 'individual']),
            'country_id' => Country::factory(),
        ];
    }

    public function Museum(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'museum',
        ]);
    }

    public function Institution(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'institution',
        ]);
    }

    public function Individual(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'individual',
        ]);
    }
}

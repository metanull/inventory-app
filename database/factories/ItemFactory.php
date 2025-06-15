<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Partner;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
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
            'partner_id' => Partner::factory(),
            'internal_name' => $this->faker->unique()->words(2, true),
            'backward_compatibility' => $this->faker->optional()->lexify('???'),
            'type' => $this->faker->randomElement(['object', 'monument']),
            'project_id' => null, // This should be set to a valid project ID if needed
            'country_id' => null, // This should be set to a valid country ID if needed
        ];
    }

    public function withProject(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'project_id' => Project::factory(),
            ];
        });
    }

    public function withCountry(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'country_id' => Country::factory(),
            ];
        });
    }

    public function Object(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'object',
        ]);
    }

    public function Monument(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'monument',
        ]);
    }
}

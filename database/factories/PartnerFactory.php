<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Item;
use App\Models\Project;
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
            'backward_compatibility' => $this->faker->bothify('???_##'),
            'type' => $this->faker->randomElement(['museum', 'institution', 'individual']),
            'country_id' => null, // This should be set to a valid country ID if needed
            // GPS Location
            'latitude' => $this->faker->optional(0.7)->latitude(),
            'longitude' => $this->faker->optional(0.7)->longitude(),
            'map_zoom' => $this->faker->numberBetween(12, 18),
            // Relationships
            'project_id' => null,
            'monument_item_id' => null,
            // Visibility
            'visible' => $this->faker->boolean(70), // 70% visible by default
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

    public function withCountry(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'country_id' => Country::factory(),
            ];
        });
    }

    /**
     * Indicate that the partner should have GPS coordinates.
     */
    public function withGPS(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'latitude' => $this->faker->latitude(),
                'longitude' => $this->faker->longitude(),
                'map_zoom' => $this->faker->numberBetween(14, 18),
            ];
        });
    }

    /**
     * Indicate that the partner should be associated with a project.
     */
    public function withProject(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'project_id' => Project::factory(),
            ];
        });
    }

    /**
     * Indicate that the partner should be linked to a monument item.
     */
    public function withMonument(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'monument_item_id' => Item::factory()->monument(),
            ];
        });
    }

    /**
     * Indicate that the partner should be visible.
     */
    public function visible(): self
    {
        return $this->state(fn (array $attributes) => [
            'visible' => true,
        ]);
    }

    /**
     * Indicate that the partner should be hidden.
     */
    public function hidden(): self
    {
        return $this->state(fn (array $attributes) => [
            'visible' => false,
        ]);
    }
}

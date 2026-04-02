<?php

namespace Database\Factories;

use App\Models\Dynasty;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Dynasty>
 */
class DynastyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fromAd = $this->faker->numberBetween(600, 1800);
        $toAd = $this->faker->numberBetween($fromAd, 1900);

        return [
            'id' => $this->faker->unique()->uuid(),
            'from_ah' => $this->faker->optional(0.7)->numberBetween(1, 1200),
            'to_ah' => $this->faker->optional(0.7)->numberBetween(100, 1400),
            'from_ad' => $this->faker->optional(0.7)->passthrough($fromAd),
            'to_ad' => $this->faker->optional(0.7)->passthrough($toAd),
            'backward_compatibility' => $this->faker->optional()->bothify('mwnf3:dynasties:##'),
        ];
    }
}

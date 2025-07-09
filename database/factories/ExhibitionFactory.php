<?php

namespace Database\Factories;

use App\Models\Exhibition;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExhibitionFactory extends Factory
{
    protected $model = Exhibition::class;

    public function definition(): array
    {
        return [
            'internal_name' => $this->faker->unique()->slug(3),
            'backward_compatibility' => null,
        ];
    }

    /**
     * Create an exhibition with backward compatibility.
     */
    public function withBackwardCompatibility(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'backward_compatibility' => $this->faker->uuid(),
            ];
        });
    }
}

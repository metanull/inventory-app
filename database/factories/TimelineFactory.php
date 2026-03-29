<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Timeline;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Timeline>
 */
class TimelineFactory extends Factory
{
    protected $model = Timeline::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->unique()->uuid(),
            'internal_name' => $this->faker->unique()->slug(3),
            'country_id' => Country::factory(),
            'collection_id' => null,
            'backward_compatibility' => $this->faker->optional()->bothify('mwnf3:hcr:country:##'),
            'extra' => null,
        ];
    }
}

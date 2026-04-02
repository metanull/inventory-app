<?php

namespace Database\Factories;

use App\Models\Timeline;
use App\Models\TimelineEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimelineEvent>
 */
class TimelineEventFactory extends Factory
{
    protected $model = TimelineEvent::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $yearFrom = $this->faker->numberBetween(600, 1800);

        return [
            'id' => $this->faker->unique()->uuid(),
            'timeline_id' => Timeline::factory(),
            'internal_name' => $this->faker->unique()->slug(3),
            'year_from' => $yearFrom,
            'year_to' => $this->faker->numberBetween($yearFrom, 2000),
            'year_from_ah' => $this->faker->optional(0.5)->numberBetween(1, 1200),
            'year_to_ah' => $this->faker->optional(0.5)->numberBetween(100, 1400),
            'date_from' => null,
            'date_to' => null,
            'display_order' => $this->faker->numberBetween(1, 100),
            'backward_compatibility' => $this->faker->optional()->bothify('mwnf3:hcr:##'),
            'extra' => null,
        ];
    }
}

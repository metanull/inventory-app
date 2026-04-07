<?php

namespace Database\Factories;

use App\Models\Language;
use App\Models\TimelineEvent;
use App\Models\TimelineEventTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TimelineEventTranslation>
 */
class TimelineEventTranslationFactory extends Factory
{
    protected $model = TimelineEventTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->unique()->uuid(),
            'timeline_event_id' => TimelineEvent::factory(),
            'language_id' => Language::factory(),
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->optional(0.5)->paragraph(),
            'date_from_description' => $this->faker->optional(0.3)->sentence(2),
            'date_to_description' => $this->faker->optional(0.3)->sentence(2),
            'date_from_ah_description' => $this->faker->optional(0.2)->sentence(2),
            'backward_compatibility' => null,
            'extra' => null,
        ];
    }
}

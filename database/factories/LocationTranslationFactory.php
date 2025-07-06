<?php

namespace Database\Factories;

use App\Models\Language;
use App\Models\Location;
use App\Models\LocationTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LocationTranslation>
 */
class LocationTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LocationTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'location_id' => Location::factory(),
            'language_id' => Language::factory(),
            'name' => $this->faker->city,
            'description' => $this->faker->paragraph,
        ];
    }

    /**
     * Create a translation without creating translations for the parent location.
     */
    public function withoutLocationTranslations(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'location_id' => Location::factory()->withoutTranslations(),
            ];
        });
    }
}

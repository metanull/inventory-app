<?php

namespace Database\Factories;

use App\Models\Language;
use App\Models\Province;
use App\Models\ProvinceTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProvinceTranslation>
 */
class ProvinceTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProvinceTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'province_id' => Province::factory(),
            'language_id' => Language::factory(),
            'name' => $this->faker->state,
            'description' => $this->faker->paragraph,
        ];
    }

    /**
     * Create a translation without creating translations for the parent province.
     */
    public function withoutProvinceTranslations(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'province_id' => Province::factory()->withoutTranslations(),
            ];
        });
    }
}

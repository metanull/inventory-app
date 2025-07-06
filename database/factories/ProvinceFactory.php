<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Province;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Province>
 */
class ProvinceFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Province::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'internal_name' => $this->faker->unique()->words(2, true),
            'country_id' => function () {
                return Country::inRandomOrder()->first()?->id ?? Country::factory()->create()->id;
            },
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Province $province) {
            // Automatically create language entries when a province is created
            $languages = \App\Models\Language::inRandomOrder()->take(rand(1, 3))->get();

            foreach ($languages as $language) {
                $province->languages()->attach($language->id, [
                    'name' => $this->faker->words(2, true),
                ]);
            }
        });
    }
}

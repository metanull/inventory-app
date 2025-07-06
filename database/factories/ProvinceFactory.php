<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Language;
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
            'country_id' => Country::factory(),
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
            // Use existing languages if available, otherwise create new ones
            $existingLanguages = Language::limit(3)->get();

            if ($existingLanguages->count() >= 1) {
                $languages = $existingLanguages->random(min($existingLanguages->count(), rand(1, 3)));
            } else {
                $languages = Language::factory(rand(1, 3))->create();
            }

            foreach ($languages as $language) {
                $province->languages()->attach($language->id, [
                    'name' => $this->faker->words(2, true),
                ]);
            }
        });
    }
}

<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\CountryTranslation;
use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CountryTranslation>
 */
class CountryTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CountryTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'country_id' => Country::factory(),
            'language_id' => Language::factory(),
            'name' => $this->faker->country(),
            'backward_compatibility' => $this->faker->optional()->bothify('???_##'),
            'extra' => $this->faker->optional()->passthrough(['key' => 'value']),
        ];
    }

    /**
     * Create a translation for a specific country.
     */
    public function forCountry(string $countryId): static
    {
        return $this->state(function (array $attributes) use ($countryId) {
            return [
                'country_id' => $countryId,
            ];
        });
    }

    /**
     * Create a translation for a specific language.
     */
    public function forLanguage(string $languageId): static
    {
        return $this->state(function (array $attributes) use ($languageId) {
            return [
                'language_id' => $languageId,
            ];
        });
    }
}

<?php

namespace Database\Factories;

use App\Models\Language;
use App\Models\LanguageTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LanguageTranslation>
 */
class LanguageTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LanguageTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'language_id' => Language::factory(),
            'display_language_id' => Language::factory(),
            'name' => $this->faker->word(),
            'backward_compatibility' => $this->faker->optional()->bothify('???_##'),
            'extra' => $this->faker->optional()->passthrough(['key' => 'value']),
        ];
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

    /**
     * Create a translation displayed in a specific language.
     */
    public function forDisplayLanguage(string $displayLanguageId): static
    {
        return $this->state(function (array $attributes) use ($displayLanguageId) {
            return [
                'display_language_id' => $displayLanguageId,
            ];
        });
    }
}

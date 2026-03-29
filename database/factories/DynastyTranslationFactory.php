<?php

namespace Database\Factories;

use App\Models\Dynasty;
use App\Models\DynastyTranslation;
use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DynastyTranslation>
 */
class DynastyTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DynastyTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dynasty_id' => Dynasty::factory(),
            'language_id' => Language::factory(),
            'name' => $this->faker->words(3, true),
            'also_known_as' => $this->faker->optional()->words(2, true),
            'area' => $this->faker->optional()->sentence(),
            'history' => $this->faker->optional()->paragraphs(2, true),
            'date_description_ah' => $this->faker->optional()->bothify('##-### AH'),
            'date_description_ad' => $this->faker->optional()->bothify('##-### AD'),
            'backward_compatibility' => $this->faker->optional()->bothify('mwnf3:dynasty_texts:##'),
            'extra' => $this->faker->optional()->passthrough(['key' => 'value']),
        ];
    }

    /**
     * Create a translation with specific language.
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

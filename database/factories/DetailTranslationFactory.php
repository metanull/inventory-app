<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Context;
use App\Models\Detail;
use App\Models\DetailTranslation;
use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DetailTranslation>
 */
class DetailTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = DetailTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'detail_id' => Detail::factory(),
            'language_id' => Language::factory(),
            'context_id' => Context::factory(),
            'name' => $this->faker->words(3, true),
            'alternate_name' => $this->faker->optional()->words(3, true),
            'description' => $this->faker->paragraph,
            'author_id' => $this->faker->optional()->randomElement([Author::factory(), null]),
            'text_copy_editor_id' => $this->faker->optional()->randomElement([Author::factory(), null]),
            'translator_id' => $this->faker->optional()->randomElement([Author::factory(), null]),
            'translation_copy_editor_id' => $this->faker->optional()->randomElement([Author::factory(), null]),
            'extra' => $this->faker->optional()->randomElements(['key1' => 'value1', 'key2' => 'value2']),
        ];
    }

    /**
     * Create a translation with default context.
     */
    public function defaultContext(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'context_id' => Context::factory()->default(),
            ];
        });
    }

    /**
     * Create a translation without creating translations for the parent detail.
     */
    public function withoutDetailTranslations(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'detail_id' => Detail::factory()->withoutTranslations(),
            ];
        });
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

    /**
     * Create a translation with specific context.
     */
    public function forContext(string $contextId): static
    {
        return $this->state(function (array $attributes) use ($contextId) {
            return [
                'context_id' => $contextId,
            ];
        });
    }

    /**
     * Create a translation with all author fields filled.
     */
    public function withAllAuthors(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'author_id' => Author::factory(),
                'text_copy_editor_id' => Author::factory(),
                'translator_id' => Author::factory(),
                'translation_copy_editor_id' => Author::factory(),
            ];
        });
    }
}

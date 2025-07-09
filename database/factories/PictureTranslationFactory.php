<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Context;
use App\Models\Language;
use App\Models\Picture;
use App\Models\PictureTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PictureTranslation>
 */
class PictureTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PictureTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'picture_id' => Picture::factory(),
            'language_id' => Language::factory(),
            'context_id' => Context::factory(),
            'description' => $this->faker->paragraph,
            'caption' => $this->faker->sentence,
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
            // Find existing default context or create one
            $defaultContext = Context::where('is_default', true)->first() ?? Context::factory()->default()->create();

            return [
                'context_id' => $defaultContext->id,
            ];
        });
    }

    /**
     * Create a translation for a specific picture.
     */
    public function forPicture(Picture $picture): static
    {
        return $this->state(function (array $attributes) use ($picture) {
            return [
                'picture_id' => $picture->id,
            ];
        });
    }

    /**
     * Create a translation for a specific language.
     */
    public function forLanguage(Language $language): static
    {
        return $this->state(function (array $attributes) use ($language) {
            return [
                'language_id' => $language->id,
            ];
        });
    }

    /**
     * Create a translation for a specific context.
     */
    public function forContext(Context $context): static
    {
        return $this->state(function (array $attributes) use ($context) {
            return [
                'context_id' => $context->id,
            ];
        });
    }

    /**
     * Create a translation with English language.
     */
    public function english(): static
    {
        return $this->state(function (array $attributes) {
            // Find or create English language
            $language = Language::find('eng') ?? Language::factory()->create(['id' => 'eng', 'internal_name' => 'English']);

            return [
                'language_id' => $language->id,
            ];
        });
    }

    /**
     * Create a translation with French language.
     */
    public function french(): static
    {
        return $this->state(function (array $attributes) {
            // Find or create French language
            $language = Language::find('fre') ?? Language::factory()->create(['id' => 'fre', 'internal_name' => 'French']);

            return [
                'language_id' => $language->id,
            ];
        });
    }

    /**
     * Create a translation with Spanish language.
     */
    public function spanish(): static
    {
        return $this->state(function (array $attributes) {
            // Find or create Spanish language
            $language = Language::find('spa') ?? Language::factory()->create(['id' => 'spa', 'internal_name' => 'Spanish']);

            return [
                'language_id' => $language->id,
            ];
        });
    }

    /**
     * Create a translation with Arabic language.
     */
    public function arabic(): static
    {
        return $this->state(function (array $attributes) {
            // Find or create Arabic language
            $language = Language::find('ara') ?? Language::factory()->create(['id' => 'ara', 'internal_name' => 'Arabic']);

            return [
                'language_id' => $language->id,
            ];
        });
    }

    /**
     * Create a translation with all author fields populated.
     */
    public function withAuthors(): static
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

    /**
     * Create a translation with extra data.
     */
    public function withExtra(array $extra): static
    {
        return $this->state(function (array $attributes) use ($extra) {
            return [
                'extra' => $extra,
            ];
        });
    }
}

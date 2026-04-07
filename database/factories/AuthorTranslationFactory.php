<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\AuthorTranslation;
use App\Models\Context;
use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuthorTranslation>
 */
class AuthorTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AuthorTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'author_id' => Author::factory(),
            'language_id' => Language::factory(),
            'context_id' => Context::factory(),
            'curriculum' => $this->faker->optional()->paragraphs(2, true),
            'backward_compatibility' => $this->faker->optional()->bothify('mwnf3:authors_cv:##'),
            'extra' => $this->faker->optional()->passthrough(['key' => 'value']),
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
     * Create a translation with default context.
     */
    public function withDefaultContext(): static
    {
        return $this->state(function (array $attributes) {
            $defaultContext = Context::default()->first();

            return [
                'context_id' => $defaultContext ? $defaultContext->id : Context::factory()->default(),
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
     * Create a translation for a specific author.
     */
    public function forAuthor(string $authorId): static
    {
        return $this->state(function (array $attributes) use ($authorId) {
            return [
                'author_id' => $authorId,
            ];
        });
    }
}

<?php

namespace Database\Factories;

use App\Models\Collection;
use App\Models\CollectionTranslation;
use App\Models\Context;
use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CollectionTranslation>
 */
class CollectionTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CollectionTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'collection_id' => Collection::factory(),
            'language_id' => Language::factory(),
            'context_id' => Context::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(3),
            'url' => $this->faker->optional(0.3)->url(),
            'backward_compatibility' => $this->faker->optional()->uuid(),
            'extra' => $this->faker->optional()->randomElement([
                null,
                ['notes' => $this->faker->sentence()],
                ['metadata' => ['author' => $this->faker->name()]],
            ]),
        ];
    }

    /**
     * Configure the factory to use an existing collection.
     */
    public function forCollection(string $collectionId): Factory
    {
        return $this->state(fn (array $attributes) => [
            'collection_id' => $collectionId,
        ]);
    }

    /**
     * Configure the factory to use an existing language.
     */
    public function withLanguage(string $languageId): Factory
    {
        return $this->state(fn (array $attributes) => [
            'language_id' => $languageId,
        ]);
    }

    /**
     * Configure the factory to use an existing context.
     */
    public function withContext(string $contextId): Factory
    {
        return $this->state(fn (array $attributes) => [
            'context_id' => $contextId,
        ]);
    }

    /**
     * Configure the factory with a URL.
     */
    public function withUrl(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'url' => $this->faker->url(),
        ]);
    }

    /**
     * Configure the factory without a URL.
     */
    public function withoutUrl(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'url' => null,
        ]);
    }
}

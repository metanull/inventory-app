<?php

namespace Database\Factories;

use App\Models\Context;
use App\Models\Gallery;
use App\Models\GalleryTranslation;
use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GalleryTranslation>
 */
class GalleryTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = GalleryTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'gallery_id' => Gallery::factory(),
            'language_id' => Language::factory(),
            'context_id' => Context::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(3),
            'url' => $this->faker->optional(0.3)->url(),
            'backward_compatibility' => null,
            'extra' => null,
        ];
    }

    /**
     * Indicate that the translation has backward compatibility data.
     */
    public function withBackwardCompatibility(): static
    {
        return $this->state(fn (array $attributes) => [
            'backward_compatibility' => $this->faker->uuid(),
        ]);
    }

    /**
     * Indicate that the translation has extra data.
     */
    public function withExtra(): static
    {
        return $this->state(fn (array $attributes) => [
            'extra' => [
                'keywords' => $this->faker->words(3),
                'curator' => $this->faker->name(),
                'theme' => $this->faker->word(),
            ],
        ]);
    }

    /**
     * Configure the factory for a specific gallery.
     */
    public function forGallery(Gallery $gallery): static
    {
        return $this->state(fn (array $attributes) => [
            'gallery_id' => $gallery->id,
        ]);
    }

    /**
     * Configure the factory for a specific language.
     */
    public function forLanguage(Language $language): static
    {
        return $this->state(fn (array $attributes) => [
            'language_id' => $language->id,
        ]);
    }

    /**
     * Configure the factory for a specific context.
     */
    public function forContext(Context $context): static
    {
        return $this->state(fn (array $attributes) => [
            'context_id' => $context->id,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\ItemItemLink;
use App\Models\ItemItemLinkTranslation;
use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ItemItemLinkTranslation>
 */
class ItemItemLinkTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ItemItemLinkTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_item_link_id' => ItemItemLink::factory(),
            'language_id' => Language::factory(),
            'description' => $this->faker->sentence(),
            'reciprocal_description' => $this->faker->optional()->sentence(),
            'backward_compatibility' => null,
        ];
    }

    /**
     * Indicate that the translation is for a specific link.
     */
    public function forLink(ItemItemLink $link): static
    {
        return $this->state(fn (array $attributes) => [
            'item_item_link_id' => $link->id,
        ]);
    }

    /**
     * Indicate that the translation is for a specific language.
     */
    public function forLanguage(Language|string $language): static
    {
        $languageId = $language instanceof Language ? $language->id : $language;

        return $this->state(fn (array $attributes) => [
            'language_id' => $languageId,
        ]);
    }

    /**
     * Indicate that the translation has backward compatibility.
     */
    public function withBackwardCompatibility(?string $backwardCompatibility = null): static
    {
        return $this->state(fn (array $attributes) => [
            'backward_compatibility' => $backwardCompatibility ?? $this->faker->uuid(),
        ]);
    }

    /**
     * Indicate that the translation has a reciprocal description.
     */
    public function withReciprocalDescription(?string $description = null): static
    {
        return $this->state(fn (array $attributes) => [
            'reciprocal_description' => $description ?? $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the translation has no reciprocal description.
     */
    public function withoutReciprocalDescription(): static
    {
        return $this->state(fn (array $attributes) => [
            'reciprocal_description' => null,
        ]);
    }
}

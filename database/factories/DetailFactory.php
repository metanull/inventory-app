<?php

namespace Database\Factories;

use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Detail>
 */
class DetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->unique()->uuid(),
            'item_id' => Item::factory(),
            'internal_name' => $this->faker->unique()->words(3, true),
            'backward_compatibility' => $this->faker->bothify('??;???;###;###'),
        ];
    }

    /**
     * Indicate that the detail should be created together with an item.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withItem(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'item_id' => Item::factory(),
            ];
        });
    }

    /**
     * Create a detail without creating translations.
     */
    public function withoutTranslations(): self
    {
        return $this->state(function (array $attributes) {
            // This state doesn't change the detail but signals to translation factories
            // not to create translations for this detail
            return [];
        });
    }
}

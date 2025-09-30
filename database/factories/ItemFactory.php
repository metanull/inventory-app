<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Partner;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Item>
 */
class ItemFactory extends Factory
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
            'partner_id' => null, // This must be set to a valid partner ID
            'parent_id' => null, // This should be set for detail and picture types
            'internal_name' => $this->faker->unique()->words(3, true),
            'backward_compatibility' => $this->faker->lexify('???'),
            'type' => $this->faker->randomElement(['object', 'monument']), // Default to top-level types
            'project_id' => null, // This should be set to a valid project ID if needed
            'country_id' => null, // This should be set to a valid country ID if needed
            'owner_reference' => $this->faker->bothify('???##'),
            'mwnf_reference' => $this->faker->bothify('???##'),
        ];
    }

    public function withPartner(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'partner_id' => Partner::factory(),
            ];
        });
    }

    public function withProject(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'project_id' => Project::factory(),
            ];
        });
    }

    public function withCountry(): self
    {
        return $this->state(function (array $attributes) {
            return [
                'country_id' => Country::factory(),
            ];
        });
    }

    public function Object(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'object',
        ]);
    }

    public function Monument(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'monument',
        ]);
    }

    public function Detail(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'detail',
            'parent_id' => \App\Models\Item::factory()->Object(), // Details must have a parent
        ]);
    }

    public function Picture(): self
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'picture',
            'parent_id' => \App\Models\Item::factory(), // Pictures can have any type of parent
        ]);
    }

    public function withParent(\App\Models\Item $parent): self
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
        ]);
    }

    /**
     * Create an item without creating translations.
     */
    public function withoutTranslations(): self
    {
        return $this->state(function (array $attributes) {
            // This state doesn't change the item but signals to translation factories
            // not to create translations for this item
            return [];
        });
    }
}

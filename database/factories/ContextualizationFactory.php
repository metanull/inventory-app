<?php

namespace Database\Factories;

use App\Models\Context;
use App\Models\Detail;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contextualization>
 */
class ContextualizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Randomly decide whether to associate with Item or Detail
        $associateWithItem = $this->faker->boolean();

        return [
            'context_id' => Context::factory(),
            'item_id' => $associateWithItem ? Item::factory() : null,
            'detail_id' => ! $associateWithItem ? Detail::factory() : null,
            'extra' => null,
            'internal_name' => $this->faker->unique()->slug(3),
            'backward_compatibility' => $this->faker->optional()->uuid(),
        ];
    }

    /**
     * Associate the contextualization with an item.
     */
    public function forItem(?Item $item = null): static
    {
        return $this->state(fn (array $attributes) => [
            'item_id' => $item?->id ?? Item::factory(),
            'detail_id' => null,
        ]);
    }

    /**
     * Associate the contextualization with a detail.
     */
    public function forDetail(?Detail $detail = null): static
    {
        return $this->state(fn (array $attributes) => [
            'item_id' => null,
            'detail_id' => $detail?->id ?? Detail::factory(),
        ]);
    }

    /**
     * Associate the contextualization with the default context.
     */
    public function withDefaultContext(): static
    {
        return $this->state(fn (array $attributes) => [
            'context_id' => Context::default()->first()?->id ?? Context::factory()->create(['is_default' => true])->id,
        ]);
    }
}

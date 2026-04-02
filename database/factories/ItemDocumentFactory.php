<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\ItemDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ItemDocument>
 */
class ItemDocumentFactory extends Factory
{
    protected $model = ItemDocument::class;

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
            'language_id' => null,
            'path' => $this->faker->uuid().'.pdf',
            'original_name' => $this->faker->slug(2).'.pdf',
            'mime_type' => 'application/pdf',
            'size' => $this->faker->numberBetween(10000, 5000000),
            'title' => $this->faker->optional(0.5)->sentence(3),
            'display_order' => $this->faker->numberBetween(1, 10),
            'extra' => null,
            'backward_compatibility' => null,
        ];
    }

    /**
     * Indicate that the document should be for a specific item.
     */
    public function forItem(Item $item): static
    {
        return $this->state(fn (array $attributes) => [
            'item_id' => $item->id,
        ]);
    }
}

<?php

namespace Database\Factories;

use App\Models\Context;
use App\Models\Item;
use App\Models\ItemItemLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ItemItemLink>
 */
class ItemItemLinkFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ItemItemLink::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source_id' => Item::factory(),
            'target_id' => Item::factory(),
            'context_id' => Context::factory(),
        ];
    }

    /**
     * Indicate that the link should have specific source and target items.
     */
    public function between(Item $source, Item $target): static
    {
        return $this->state(fn (array $attributes) => [
            'source_id' => $source->id,
            'target_id' => $target->id,
        ]);
    }

    /**
     * Indicate that the link should be in a specific context.
     */
    public function inContext(Context $context): static
    {
        return $this->state(fn (array $attributes) => [
            'context_id' => $context->id,
        ]);
    }

    /**
     * Indicate that the link should use a specific source item.
     */
    public function fromSource(Item $source): static
    {
        return $this->state(fn (array $attributes) => [
            'source_id' => $source->id,
        ]);
    }

    /**
     * Indicate that the link should use a specific target item.
     */
    public function toTarget(Item $target): static
    {
        return $this->state(fn (array $attributes) => [
            'target_id' => $target->id,
        ]);
    }
}

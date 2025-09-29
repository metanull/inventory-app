<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ItemImage>
 */
class ItemImageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_id' => \App\Models\Item::factory(),
            'path' => fake()->imageUrl(800, 600, 'objects', true),
            'original_name' => fake()->word().'_'.fake()->randomNumber(4).'.jpg',
            'mime_type' => fake()->randomElement(['image/jpeg', 'image/png', 'image/webp']),
            'size' => fake()->numberBetween(50000, 2000000), // 50KB to 2MB
            'alt_text' => fake()->sentence(6),
            'display_order' => fake()->numberBetween(1, 10),
        ];
    }

    /**
     * Indicate that the image should be for a specific item.
     */
    public function forItem(\App\Models\Item $item): static
    {
        return $this->state(fn (array $attributes) => [
            'item_id' => $item->id,
        ]);
    }

    /**
     * Indicate that the image should have a specific display order.
     */
    public function withOrder(int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'display_order' => $order,
        ]);
    }

    /**
     * Create an image with realistic museum object characteristics.
     */
    public function museumObject(): static
    {
        return $this->state(fn (array $attributes) => [
            'path' => fake()->imageUrl(1200, 800, 'objects', true),
            'original_name' => 'museum_object_'.fake()->uuid().'.jpg',
            'mime_type' => 'image/jpeg',
            'size' => fake()->numberBetween(200000, 3000000), // 200KB to 3MB
            'alt_text' => 'Museum object: '.fake()->words(4, true),
        ]);
    }

    /**
     * Create an image with realistic monument characteristics.
     */
    public function monument(): static
    {
        return $this->state(fn (array $attributes) => [
            'path' => fake()->imageUrl(1600, 1200, 'architecture', true),
            'original_name' => 'monument_'.fake()->uuid().'.jpg',
            'mime_type' => 'image/jpeg',
            'size' => fake()->numberBetween(300000, 5000000), // 300KB to 5MB
            'alt_text' => 'Historical monument: '.fake()->words(5, true),
        ]);
    }
}

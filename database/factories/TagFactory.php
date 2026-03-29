<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['keyword', 'material', 'artist', 'dynasty', 'subject', 'type', 'filter', 'image-type', null];

        // Language_id is optional - default to null to avoid foreign key issues
        // Tests that need specific languages should explicitly set language_id

        return [
            'id' => $this->faker->unique()->uuid(),
            'internal_name' => $this->faker->unique()->words(3, true),
            'category' => $this->faker->randomElement($categories),
            'language_id' => null, // Nullable by default to avoid FK issues in tests
            'backward_compatibility' => $this->faker->lexify('???'),
            'description' => $this->faker->words(5, true),
        ];
    }

    /**
     * Indicate that the tag is of keyword category.
     */
    public function keyword(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'keyword',
        ]);
    }

    /**
     * Indicate that the tag is of material category.
     */
    public function material(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'material',
        ]);
    }

    /**
     * Indicate that the tag is of artist category.
     */
    public function artist(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'artist',
        ]);
    }

    /**
     * Indicate that the tag is of dynasty category.
     */
    public function dynasty(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'dynasty',
        ]);
    }

    /**
     * Indicate that the tag is of subject category.
     */
    public function subject(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'subject',
        ]);
    }

    /**
     * Indicate that the tag is of type category.
     */
    public function type(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'type',
        ]);
    }

    /**
     * Indicate that the tag is of filter category.
     */
    public function filter(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'filter',
        ]);
    }

    /**
     * Indicate that the tag is of image-type category.
     */
    public function imageType(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'image-type',
        ]);
    }
}

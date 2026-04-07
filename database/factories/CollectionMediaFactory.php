<?php

namespace Database\Factories;

use App\Enums\MediaType;
use App\Models\Collection;
use App\Models\CollectionMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CollectionMedia>
 */
class CollectionMediaFactory extends Factory
{
    protected $model = CollectionMedia::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->unique()->uuid(),
            'collection_id' => Collection::factory(),
            'language_id' => null,
            'type' => $this->faker->randomElement(MediaType::cases()),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->optional(0.5)->paragraph(),
            'url' => 'https://www.youtube.com/watch?v='.$this->faker->regexify('[a-zA-Z0-9_-]{11}'),
            'display_order' => $this->faker->numberBetween(1, 10),
            'extra' => null,
            'backward_compatibility' => null,
        ];
    }

    /**
     * Indicate that the media is audio.
     */
    public function audio(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => MediaType::AUDIO,
        ]);
    }

    /**
     * Indicate that the media is video.
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => MediaType::VIDEO,
        ]);
    }

    /**
     * Indicate that the media should be for a specific collection.
     */
    public function forCollection(Collection $collection): static
    {
        return $this->state(fn (array $attributes) => [
            'collection_id' => $collection->id,
        ]);
    }
}

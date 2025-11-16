<?php

namespace Database\Factories;

use App\Models\Collection;
use App\Models\Context;
use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Collection>
 */
class CollectionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Collection::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'internal_name' => $this->faker->unique()->slug(2),
            'type' => $this->faker->randomElement(['collection', 'exhibition', 'gallery', 'theme', 'exhibition trail', 'itinerary', 'location']),
            'language_id' => Language::factory(),
            'context_id' => Context::factory(),
            'parent_id' => null,
            'backward_compatibility' => $this->faker->optional()->uuid(),
        ];
    }

    /**
     * Configure the factory to use an existing language.
     */
    public function withLanguage(string $languageId): Factory
    {
        return $this->state(fn (array $attributes) => [
            'language_id' => $languageId,
        ]);
    }

    /**
     * Configure the factory to use an existing context.
     */
    public function withContext(string $contextId): Factory
    {
        return $this->state(fn (array $attributes) => [
            'context_id' => $contextId,
        ]);
    }

    /**
     * Configure the factory to use the default language.
     */
    public function withDefaultLanguage(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'language_id' => Language::where('is_default', true)->first()?->id ?? 'eng',
        ]);
    }

    /**
     * Configure the factory to use the default context.
     */
    public function withDefaultContext(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'context_id' => Context::where('is_default', true)->first()?->id,
        ]);
    }

    /**
     * Configure the factory to create a collection with partners.
     */
    public function hasPartners(int $count = 1): Factory
    {
        return $this->afterCreating(function (Collection $collection) use ($count) {
            $partners = \App\Models\Partner::factory()->count($count)->create();
            foreach ($partners as $partner) {
                $collection->partners()->attach($partner->id, [
                    'collection_type' => 'collection',
                    'level' => \App\Enums\PartnerLevel::PARTNER->value,
                ]);
            }
        });
    }

    /**
     * Create a collection of type 'collection'.
     */
    public function collection(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'collection',
        ]);
    }

    /**
     * Create a collection of type 'exhibition'.
     */
    public function exhibition(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'exhibition',
        ]);
    }

    /**
     * Create a collection of type 'gallery'.
     */
    public function gallery(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'gallery',
        ]);
    }

    /**
     * Create a collection of type 'theme'.
     */
    public function theme(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'theme',
        ]);
    }

    /**
     * Create a collection of type 'exhibition trail'.
     */
    public function exhibitionTrail(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'exhibition trail',
        ]);
    }

    /**
     * Create a collection of type 'itinerary'.
     */
    public function itinerary(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'itinerary',
        ]);
    }

    /**
     * Create a collection of type 'location'.
     */
    public function location(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'location',
        ]);
    }

    /**
     * Configure the factory to use an existing parent collection.
     */
    public function withParent(string $parentId): Factory
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parentId,
        ]);
    }

    /**
     * Configure the factory to create a child collection.
     */
    public function asChild(): Factory
    {
        return $this->afterMaking(function (Collection $collection) {
            if (! $collection->parent_id) {
                $parent = Collection::factory()->create();
                $collection->parent_id = $parent->id;
            }
        });
    }
}

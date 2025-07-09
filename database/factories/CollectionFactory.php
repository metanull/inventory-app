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
            'language_id' => Language::factory(),
            'context_id' => Context::factory(),
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
}

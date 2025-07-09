<?php

namespace Database\Factories;

use App\Models\Context;
use App\Models\Exhibition;
use App\Models\ExhibitionTranslation;
use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExhibitionTranslationFactory extends Factory
{
    protected $model = ExhibitionTranslation::class;

    public function definition(): array
    {
        return [
            'exhibition_id' => Exhibition::factory(),
            'language_id' => Language::factory(),
            'context_id' => Context::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'url' => $this->faker->optional()->url(),
            'backward_compatibility' => null,
            'extra' => null,
        ];
    }

    /**
     * Create a translation with a URL.
     */
    public function withUrl(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'url' => $this->faker->url(),
            ];
        });
    }

    /**
     * Create a translation with extra data.
     */
    public function withExtra(array $extra): static
    {
        return $this->state(function (array $attributes) use ($extra) {
            return [
                'extra' => $extra,
            ];
        });
    }

    /**
     * Create a translation with backward compatibility.
     */
    public function withBackwardCompatibility(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'backward_compatibility' => $this->faker->uuid(),
            ];
        });
    }
}

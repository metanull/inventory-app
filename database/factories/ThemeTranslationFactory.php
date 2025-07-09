<?php

namespace Database\Factories;

use App\Models\Context;
use App\Models\Language;
use App\Models\Theme;
use App\Models\ThemeTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThemeTranslationFactory extends Factory
{
    protected $model = ThemeTranslation::class;

    public function definition(): array
    {
        return [
            'theme_id' => Theme::factory(),
            'language_id' => Language::factory(),
            'context_id' => Context::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'introduction' => $this->faker->paragraph(),
            'backward_compatibility' => null,
            'extra' => null,
        ];
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

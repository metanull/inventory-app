<?php

namespace Database\Factories;

use App\Models\Exhibition;
use App\Models\Picture;
use App\Models\Theme;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThemeFactory extends Factory
{
    protected $model = Theme::class;

    public function definition(): array
    {
        return [
            'exhibition_id' => Exhibition::factory(),
            'parent_id' => null,
            'internal_name' => $this->faker->unique()->slug(3),
            'backward_compatibility' => null,
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Theme $theme) {
            if (Picture::count() > 0) {
                $theme->pictures()->attach(Picture::inRandomOrder()->first()->id);
            }
        });
    }

    /**
     * Create a theme with backward compatibility.
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

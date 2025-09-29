<?php

namespace Database\Factories;

use App\Models\Collection;
use App\Models\Theme;
use Illuminate\Database\Eloquent\Factories\Factory;

class ThemeFactory extends Factory
{
    protected $model = Theme::class;

    public function definition(): array
    {
        return [
            'collection_id' => Collection::factory()->state(['type' => 'exhibition']),
            'parent_id' => null,
            'internal_name' => $this->faker->unique()->slug(3),
            'backward_compatibility' => null,
        ];
    }

    public function configure()
    {
        // Legacy Picture attachment removed - Theme images now handled via ItemImage for associated items
        return $this;
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

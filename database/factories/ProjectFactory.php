<?php

namespace Database\Factories;

use App\Models\Context;
use App\Models\Language;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->unique()->uuid(),
            'internal_name' => $this->faker->unique()->words('3', true),
            'backward_compatibility' => $this->faker->lexify('???'),
            'launch_date' => null,
            'is_launched' => false,
            'is_enabled' => false,
            'context_id' => null, // This should be set to a valid context ID if needed
            'language_id' => null, // This should be set to a valid language ID if needed
        ];
    }

    public function withEnabled(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'is_enabled' => true,
        ]);
    }

    public function withLaunched(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'is_launched' => true,
            'launch_date' => $this->faker->date(),
        ]);
    }

    public function withContext(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'context_id' => Context::factory(),
            ];
        });
    }

    public function withLanguage(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'language_id' => Language::factory(),
            ];
        });
    }
}

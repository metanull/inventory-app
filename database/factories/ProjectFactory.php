<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Project;

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
            'internal_name' => $this->faker->unique()->word(),
            'backward_compatibility' => $this->faker->optional()->word(),
            'launch_date' => $this->faker->optional()->date(),
            'is_launched' => $this->faker->boolean(),
            'is_enabled' => $this->faker->boolean(),
            'context_id' => null, // This should be set to a valid context ID if needed
            'language_id' => null, // This should be set to a valid language ID if needed
        ];
    }

    public function launched_enabled(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'is_launched' => true,
            'is_enabled' => true,
        ]);
    }

    public function launched_disabled(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'is_launched' => true,
            'is_enabled' => false,
        ]);
    }

    public function not_launched(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'is_launched' => false,
        ]);
    }
}

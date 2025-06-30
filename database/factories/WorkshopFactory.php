<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Workshop>
 */
class WorkshopFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement([
                'Real Fábrica de Cristales de La Granja',
                'Royal Porcelain Factory',
                'Imperial Glass Workshop',
                'Medici Workshop',
                'Venetian Glass Atelier',
            ]).' ('.$this->faker->city().')',
            'internal_name' => $this->faker->company().'_workshop',
            'backward_compatibility' => $this->faker->optional(0.3)->uuid(),
        ];
    }
}

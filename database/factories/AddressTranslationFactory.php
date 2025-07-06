<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\AddressTranslation;
use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AddressTranslation>
 */
class AddressTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AddressTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'address_id' => Address::factory(),
            'language_id' => Language::factory(),
            'address' => $this->faker->streetAddress,
            'description' => $this->faker->paragraph,
        ];
    }

    /**
     * Create a translation without creating translations for the parent address.
     */
    public function withoutAddressTranslations(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'address_id' => Address::factory()->withoutTranslations(),
            ];
        });
    }
}

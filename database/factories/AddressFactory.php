<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Address::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'internal_name' => $this->faker->unique()->words(3, true),
            'country_id' => function () {
                return Country::inRandomOrder()->first()?->id ?? Country::factory()->create()->id;
            },
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Address $address) {
            // Automatically create language entries when an address is created
            $languages = \App\Models\Language::inRandomOrder()->take(rand(1, 3))->get();

            foreach ($languages as $language) {
                $address->languages()->attach($language->id, [
                    'address' => $this->faker->streetAddress().', '.$this->faker->city().', '.$this->faker->country(),
                    'description' => $this->faker->optional(0.7)->sentence(),
                ]);
            }
        });
    }
}

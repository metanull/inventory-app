<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\Country;
use App\Models\Language;
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
            'country_id' => Country::factory(),
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
            // Use existing languages if available, otherwise create new ones
            $existingLanguages = Language::limit(3)->get();

            if ($existingLanguages->count() >= 1) {
                $languages = $existingLanguages->random(min($existingLanguages->count(), rand(1, 3)));
            } else {
                $languages = Language::factory(rand(1, 3))->create();
            }

            foreach ($languages as $language) {
                $address->translations()->create([
                    'language_id' => $language->id,
                    'address' => $this->faker->streetAddress().', '.$this->faker->city().', '.$this->faker->country(),
                    'description' => $this->faker->optional(0.7)->sentence(),
                ]);
            }
        });
    }

    /**
     * Create an address without translations.
     *
     * @return static
     */
    public function withoutTranslations()
    {
        return $this->afterCreating(function (Address $address) {
            // Don't create any translations
        });
    }
}

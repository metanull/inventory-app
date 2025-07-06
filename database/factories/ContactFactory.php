<?php

namespace Database\Factories;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contact>
 */
class ContactFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Contact::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'internal_name' => $this->faker->unique()->words(3, true),
            'phone_number' => $this->generateValidPhoneNumber(),
            'fax_number' => $this->faker->optional(0.7)->passthrough($this->generateValidPhoneNumber()),
            'email' => $this->faker->optional(0.9)->safeEmail(),
        ];
    }

    /**
     * Generate a valid international phone number that can be parsed by PhoneNumber library.
     */
    private function generateValidPhoneNumber(): string
    {
        // Generate a valid US phone number in international format
        $areaCode = $this->faker->numberBetween(200, 999);
        $exchange = $this->faker->numberBetween(200, 999);
        $number = $this->faker->numberBetween(1000, 9999);

        return "+1{$areaCode}{$exchange}{$number}";
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Contact $contact) {
            // Generate random labels for random languages
            $languages = \App\Models\Language::inRandomOrder()->take(rand(1, 3))->get();

            foreach ($languages as $language) {
                $contact->languages()->attach($language->id, [
                    'label' => $this->faker->sentence(2),
                ]);
            }
        });
    }
}

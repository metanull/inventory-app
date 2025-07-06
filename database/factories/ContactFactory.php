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
            // Use existing languages if available, otherwise create new ones
            $existingLanguages = \App\Models\Language::limit(3)->get();

            if ($existingLanguages->count() >= 1) {
                $languages = $existingLanguages->random(min($existingLanguages->count(), rand(1, 3)));
            } else {
                $languages = \App\Models\Language::factory(rand(1, 3))->create();
            }

            foreach ($languages as $language) {
                $contact->translations()->create([
                    'language_id' => $language->id,
                    'label' => $this->faker->sentence(2),
                ]);
            }
        });
    }

    /**
     * Create a contact without translations.
     *
     * @return static
     */
    public function withoutTranslations()
    {
        return $this->afterCreating(function (Contact $contact) {
            // Don't create any translations
        });
    }
}

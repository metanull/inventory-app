<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\ContactTranslation;
use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContactTranslation>
 */
class ContactTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ContactTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contact_id' => Contact::factory(),
            'language_id' => Language::factory(),
            'label' => $this->faker->word,
        ];
    }

    /**
     * Create a translation without creating translations for the parent contact.
     */
    public function withoutContactTranslations(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'contact_id' => Contact::factory()->withoutTranslations(),
            ];
        });
    }
}

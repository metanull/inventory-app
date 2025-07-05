<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\ContactLanguage;
use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContactLanguage>
 */
class ContactLanguageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ContactLanguage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contact_id' => Contact::factory(),
            'language_id' => function () {
                return Language::inRandomOrder()->first()->id;
            },
            'label' => $this->faker->sentence(2),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Glossary;
use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GlossarySpelling>
 */
class GlossarySpellingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->unique()->uuid(),
            'glossary_id' => Glossary::factory(),
            'language_id' => Language::factory(),
            'spelling' => $this->faker->word(),
        ];
    }
}

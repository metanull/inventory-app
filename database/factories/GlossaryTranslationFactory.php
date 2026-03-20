<?php

namespace Database\Factories;

use App\Models\Glossary;
use App\Models\GlossaryTranslation;
use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GlossaryTranslation>
 */
class GlossaryTranslationFactory extends Factory
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
            'definition' => $this->faker->paragraph(),
        ];
    }
}

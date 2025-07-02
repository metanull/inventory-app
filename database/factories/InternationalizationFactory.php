<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Internationalization>
 */
class InternationalizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contextualization_id' => \App\Models\Contextualization::factory(),
            'language_id' => \App\Models\Language::factory(),
            'name' => $this->faker->words(3, true),
            'alternate_name' => $this->faker->optional(0.5)->words(3, true),
            'description' => $this->faker->sentence(),
            'type' => $this->faker->optional(0.8)->randomElement([
                'Carpet', 'Manuscript', 'Glassware', 'Pottery', 'Painting', 'Sculpture',
            ]),
            'holder' => $this->faker->optional(0.7)->sentence(),
            'owner' => $this->faker->optional(0.7)->sentence(),
            'initial_owner' => $this->faker->optional(0.6)->sentence(),
            'dates' => $this->faker->optional(0.8)->sentence(),
            'location' => $this->faker->optional(0.8)->sentence(),
            'dimensions' => $this->faker->optional(0.7)->sentence(),
            'place_of_production' => $this->faker->optional(0.8)->sentence(),
            'method_for_datation' => $this->faker->optional(0.6)->sentence(),
            'method_for_provenance' => $this->faker->optional(0.6)->sentence(),
            'obtention' => $this->faker->optional(0.6)->sentence(),
            'bibliography' => $this->faker->optional(0.5)->text(500),
            'extra' => $this->faker->optional(0.3)->randomElement([
                ['notes' => $this->faker->sentence()],
                ['additional_info' => $this->faker->paragraph()],
                null,
            ]),
            'author_id' => $this->faker->optional(0.7)->passthrough(
                \App\Models\Author::factory()
            ),
            'text_copy_editor_id' => $this->faker->optional(0.5)->passthrough(
                \App\Models\Author::factory()
            ),
            'translator_id' => $this->faker->optional(0.6)->passthrough(
                \App\Models\Author::factory()
            ),
            'translation_copy_editor_id' => $this->faker->optional(0.4)->passthrough(
                \App\Models\Author::factory()
            ),
            'backward_compatibility' => $this->faker->optional(0.3)->uuid(),
        ];
    }
}

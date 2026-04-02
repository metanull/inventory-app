<?php

namespace Database\Factories;

use App\Models\Context;
use App\Models\Contributor;
use App\Models\ContributorTranslation;
use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContributorTranslation>
 */
class ContributorTranslationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker->unique()->uuid(),
            'contributor_id' => Contributor::factory(),
            'language_id' => Language::factory(),
            'context_id' => Context::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->optional()->paragraph(),
            'link' => $this->faker->optional()->url(),
            'alt_text' => $this->faker->optional()->sentence(),
            'extra' => null,
            'backward_compatibility' => $this->faker->bothify('???_##'),
        ];
    }
}

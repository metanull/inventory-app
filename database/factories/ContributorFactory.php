<?php

namespace Database\Factories;

use App\Models\Collection;
use App\Models\Contributor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contributor>
 */
class ContributorFactory extends Factory
{
    public function definition(): array
    {
        return [
            'id' => $this->faker->unique()->uuid(),
            'collection_id' => Collection::factory(),
            'category' => $this->faker->randomElement(['partner', 'cooperator', 'occasion', 'sponsor', 'full_partner', 'co_organiser', 'other_contributor']),
            'display_order' => $this->faker->numberBetween(0, 10),
            'visible' => $this->faker->boolean(80),
            'backward_compatibility' => $this->faker->bothify('???_##'),
            'internal_name' => $this->faker->unique()->words(3, true),
        ];
    }

    public function partner(): self
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'partner',
        ]);
    }

    public function cooperator(): self
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'cooperator',
        ]);
    }

    public function occasion(): self
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'occasion',
        ]);
    }

    public function sponsor(): self
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'sponsor',
        ]);
    }

    public function fullPartner(): self
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'full_partner',
        ]);
    }

    public function coOrganiser(): self
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'co_organiser',
        ]);
    }

    public function otherContributor(): self
    {
        return $this->state(fn (array $attributes) => [
            'category' => 'other_contributor',
        ]);
    }
}

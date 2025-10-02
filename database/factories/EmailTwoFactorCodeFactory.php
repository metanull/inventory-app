<?php

namespace Database\Factories;

use App\Models\EmailTwoFactorCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EmailTwoFactorCode>
 */
class EmailTwoFactorCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'code' => EmailTwoFactorCode::generateCode(),
            'expires_at' => now()->addMinutes(5),
            'used_at' => null,
        ];
    }

    /**
     * Create an expired code.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subMinutes(1),
        ]);
    }

    /**
     * Create a used code.
     */
    public function used(): static
    {
        return $this->state(fn (array $attributes) => [
            'used_at' => now()->subMinutes(1),
        ]);
    }

    /**
     * Create a valid (not expired, not used) code.
     */
    public function valid(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->addMinutes(5),
            'used_at' => null,
        ]);
    }
}

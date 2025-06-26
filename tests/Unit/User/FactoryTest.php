<?php

namespace Tests\Unit\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_factory(): void
    {
        $user = User::factory()->make();
        $this->assertInstanceOf(User::class, $user);
        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->email_verified_at);
        $this->assertNotNull($user->password);
        $this->assertNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_recovery_codes);
        $this->assertNotNull($user->remember_token);
        $this->assertNull($user->profile_photo_path);
        $this->assertNull($user->current_team_id);
    }

    public function test_factory_unverified(): void
    {
        $user = User::factory()->unverified()->make();
        $this->assertInstanceOf(User::class, $user);
        $this->assertNull($user->email_verified_at);
    }

    public function test_factory_creates_a_row_in_database(): void
    {
        $user = User::factory()->create();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'profile_photo_path' => null,
            'current_team_id' => null,
        ]);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_factory_creates_a_row_in_database_unverified(): void
    {
        $user = User::factory()->unverified()->create();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email_verified_at' => null,
        ]);
        $this->assertDatabaseCount('users', 1);
    }
}

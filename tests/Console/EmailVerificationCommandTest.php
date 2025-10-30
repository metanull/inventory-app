<?php

namespace Tests\Console;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure roles exist
        $this->artisan('db:seed', ['--class' => 'ProductionDataSeeder']);
    }

    public function test_can_check_email_verification_status_of_verified_user(): void
    {
        $user = User::factory()->create([
            'email' => 'verified@example.com',
            'email_verified_at' => now()->subDay(),
        ]);

        $this->artisan('user:email-verification', ['email' => 'verified@example.com', 'action' => 'status'])
            ->expectsOutput('Email Verification Status for \'verified@example.com\':')
            ->expectsOutput('Status: ✅ Verified')
            ->assertExitCode(0);
    }

    public function test_can_check_email_verification_status_of_unverified_user(): void
    {
        $user = User::factory()->create([
            'email' => 'unverified@example.com',
            'email_verified_at' => null,
        ]);

        $this->artisan('user:email-verification', ['email' => 'unverified@example.com', 'action' => 'status'])
            ->expectsOutput('Email Verification Status for \'unverified@example.com\':')
            ->expectsOutput('Status: ❌ Not Verified')
            ->assertExitCode(0);
    }

    public function test_can_verify_unverified_user_email(): void
    {
        $user = User::factory()->create([
            'email' => 'toverify@example.com',
            'email_verified_at' => null,
        ]);

        $this->assertFalse($user->hasVerifiedEmail());

        $this->artisan('user:email-verification', ['email' => 'toverify@example.com', 'action' => 'verify'])
            ->expectsOutput('✅ Successfully verified email for user \'toverify@example.com\'.')
            ->assertExitCode(0);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_can_unverify_verified_user_email(): void
    {
        $user = User::factory()->create([
            'email' => 'tounverify@example.com',
            'email_verified_at' => now(),
        ]);

        $this->assertTrue($user->hasVerifiedEmail());

        $this->artisan('user:email-verification', ['email' => 'tounverify@example.com', 'action' => 'unverify'])
            ->expectsOutput('❌ Successfully unverified email for user \'tounverify@example.com\'.')
            ->assertExitCode(0);

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_verifying_already_verified_user_shows_appropriate_message(): void
    {
        $user = User::factory()->create([
            'email' => 'alreadyverified@example.com',
            'email_verified_at' => now(),
        ]);

        $this->artisan('user:email-verification', ['email' => 'alreadyverified@example.com', 'action' => 'verify'])
            ->expectsOutput('✅ User \'alreadyverified@example.com\' is already verified.')
            ->assertExitCode(0);
    }

    public function test_unverifying_already_unverified_user_shows_appropriate_message(): void
    {
        $user = User::factory()->create([
            'email' => 'alreadyunverified@example.com',
            'email_verified_at' => null,
        ]);

        $this->artisan('user:email-verification', ['email' => 'alreadyunverified@example.com', 'action' => 'unverify'])
            ->expectsOutput('⚠️  User \'alreadyunverified@example.com\' is already unverified.')
            ->assertExitCode(0);
    }

    public function test_command_fails_with_invalid_email(): void
    {
        $this->artisan('user:email-verification', ['email' => 'nonexistent@example.com', 'action' => 'status'])
            ->expectsOutput('User with email \'nonexistent@example.com\' not found.')
            ->assertExitCode(1);
    }

    public function test_command_fails_with_invalid_action(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $this->artisan('user:email-verification', ['email' => 'test@example.com', 'action' => 'invalid'])
            ->expectsOutput('Invalid action \'invalid\'. Valid actions are: verify, unverify, status')
            ->assertExitCode(1);
    }
}

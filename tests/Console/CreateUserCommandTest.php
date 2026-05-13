<?php

namespace Tests\Console;

use App\Models\User;
use App\Notifications\AdminPasswordResetNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CreateUserCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_creates_a_user(): void
    {
        Notification::fake();

        $this->artisan('user:create', [
            'username' => 'Test Admin',
            'email' => 'testadmin@example.com',
        ])->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'name' => 'Test Admin',
            'email' => 'testadmin@example.com',
        ]);
    }

    public function test_command_sends_invitation_email(): void
    {
        Notification::fake();

        $this->artisan('user:create', [
            'username' => 'Invited Admin',
            'email' => 'invitedadmin@example.com',
        ])->assertExitCode(0);

        $user = User::where('email', 'invitedadmin@example.com')->firstOrFail();
        Notification::assertSentTo($user, AdminPasswordResetNotification::class);
    }

    public function test_command_does_not_output_a_plaintext_password(): void
    {
        Notification::fake();

        $this->artisan('user:create', [
            'username' => 'Secure Admin',
            'email' => 'secureadmin@example.com',
        ])
            ->expectsOutputToContain('invitation email')
            ->doesntExpectOutputToContain('Password:')
            ->assertExitCode(0);
    }

    public function test_command_sets_approved_at_on_new_user(): void
    {
        Notification::fake();

        $this->artisan('user:create', [
            'username' => 'Approved Admin',
            'email' => 'approvedadmin@example.com',
        ])->assertExitCode(0);

        $user = User::where('email', 'approvedadmin@example.com')->firstOrFail();
        $this->assertNotNull($user->approved_at);
    }
}

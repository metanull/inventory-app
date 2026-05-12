<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\UserPasswordResetService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create {username : The username for the new user} {email : The email address for the new user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new user and send a password-reset invitation link by email.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $username = $this->argument('username');
        $email = $this->argument('email');

        $user = new User;
        $user->name = $username;
        $user->email = $email;
        $user->password = Hash::make(Str::random(32));
        $user->approved_at = now();
        $user->save();

        app(UserPasswordResetService::class)->sendResetLink($user);

        $this->info('User created successfully.');
        $this->line("Username: {$username}");
        $this->line("Email: {$email}");
        $this->line('An invitation email with a password reset link has been sent.');

        return Command::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\User;

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
    protected $description = 'Create a new user with a random secure password.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $username = $this->argument('username');
        $email = $this->argument('email');
        $password = Str::random(20);

        $user = new User();
        $user->name = $username;
        $user->email = $email;
        $user->password = bcrypt($password);
        $user->save();

        $this->info("User created successfully.");
        $this->line("Username: {$username}");
        $this->line("Email: {$email}");
        $this->line("Password: {$password}");
        return Command::SUCCESS;
    }
}

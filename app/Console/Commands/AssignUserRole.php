<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class AssignUserRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:assign-role {email : User email address} {role : Role name to assign}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign a role to a user';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = $this->argument('email');
        $roleName = $this->argument('role');

        $user = User::where('email', $email)->first();
        if (! $user) {
            $this->error("User with email '{$email}' not found.");

            return Command::FAILURE;
        }

        $role = Role::where('name', $roleName)->first();
        if (! $role) {
            $this->error("Role '{$roleName}' not found.");
            $this->info('Available roles:');
            Role::all()->each(function ($role) {
                $this->line("  - {$role->name}");
            });

            return Command::FAILURE;
        }

        if ($user->hasRole($roleName)) {
            $this->info("User '{$user->name}' already has role '{$roleName}'.");

            return Command::SUCCESS;
        }

        $user->assignRole($role);

        $this->info("Successfully assigned role '{$roleName}' to user '{$user->name}' ({$email}).");

        return Command::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class RemoveUserRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:remove-role {email : User email address} {role : Role name to remove}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove a role from a user';

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

            return Command::FAILURE;
        }

        if (! $user->hasRole($roleName)) {
            $this->info("User '{$user->name}' does not have role '{$roleName}'.");

            return Command::SUCCESS;
        }

        $user->removeRole($role);

        $this->info("Successfully removed role '{$roleName}' from user '{$user->name}' ({$email}).");

        return Command::SUCCESS;
    }
}

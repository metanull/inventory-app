<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class ListUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:list {--role= : Filter by role name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all users with their roles';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $roleFilter = $this->option('role');

        $query = User::with('roles');

        if ($roleFilter) {
            $query->whereHas('roles', function ($q) use ($roleFilter) {
                $q->where('name', $roleFilter);
            });
        }

        $users = $query->get();

        if ($users->isEmpty()) {
            if ($roleFilter) {
                $this->info("No users found with role '{$roleFilter}'.");
            } else {
                $this->info('No users found.');
            }

            return Command::SUCCESS;
        }

        $headers = ['ID', 'Name', 'Email', 'Roles', 'Created'];
        $rows = [];

        foreach ($users as $user) {
            $roles = $user->roles->pluck('name')->join(', ') ?: 'No roles';
            $rows[] = [
                $user->id,
                $user->name,
                $user->email,
                $roles,
                $user->created_at->format('Y-m-d H:i'),
            ];
        }

        $this->table($headers, $rows);

        if ($roleFilter) {
            $this->info('Found '.$users->count()." user(s) with role '{$roleFilter}'.");
        } else {
            $this->info('Found '.$users->count().' user(s) total.');
        }

        return Command::SUCCESS;
    }
}

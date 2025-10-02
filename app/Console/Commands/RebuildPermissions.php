<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RebuildPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:rebuild {--force : Force rebuild without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild roles and permissions structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (! $this->option('force')) {
            if (! $this->confirm('This will clear all existing roles and permissions. Do you want to continue?')) {
                $this->info('Operation cancelled.');

                return 0;
            }
        }

        $this->info('Rebuilding permissions structure...');

        // Clear cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Clear existing roles and permissions
        $this->info('Clearing existing roles and permissions...');
        Role::query()->delete();
        Permission::query()->delete();

        // Reseed roles and permissions
        $this->info('Creating new roles and permissions...');
        $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\RolePermissionSeeder',
        ]);

        $this->info('✅ Permissions structure rebuilt successfully!');

        return 0;
    }
}

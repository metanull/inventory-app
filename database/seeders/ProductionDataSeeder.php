<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Language;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ProductionDataSeeder extends Seeder
{
    /**
     * Run the database seeds for production.
     *
     * This seeder imports the full dataset of countries, languages,
     * and creates essential roles and permissions.
     */
    public function run(): void
    {
        $this->setupRolesAndPermissions();
        $this->importCountries();
        $this->importLanguages();
    }

    /**
     * Setup essential roles and permissions.
     */
    protected function setupRolesAndPermissions(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Data operation permissions
            'view data' => 'Read access to all data models',
            'create data' => 'Create new records in data models',
            'update data' => 'Modify existing records',
            'delete data' => 'Remove records from data models',

            // User management permissions
            'manage users' => 'Create, read, update, delete users',
            'assign roles' => 'Grant and revoke user roles',
            'view user management' => 'Access user management interfaces',

            // Role management permissions
            'manage roles' => 'Create, read, update roles and permissions',
            'view role management' => 'Access role management interfaces',
        ];

        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name],
                ['description' => $description]
            );
        }

        // Create "Regular User" role with data operation permissions
        $regularUserRole = Role::firstOrCreate(
            ['name' => 'Regular User'],
            ['description' => 'Standard user with data operation access']
        );

        $regularUserRole->syncPermissions([
            'view data',
            'create data',
            'update data',
            'delete data',
        ]);

        // Create "Manager of Users" role with all permissions
        $managerRole = Role::firstOrCreate(
            ['name' => 'Manager of Users'],
            ['description' => 'User management with full data operation access']
        );

        $managerRole->syncPermissions([
            // Data operation permissions
            'view data',
            'create data',
            'update data',
            'delete data',

            // User management permissions
            'manage users',
            'assign roles',
            'view user management',

            // Role management permissions
            'manage roles',
            'view role management',
        ]);

        $this->command->info('Roles and permissions setup completed!');
        $this->command->info('Roles: Regular User, Manager of Users');
        $this->command->info('Permissions: '.count($permissions).' created/verified');
    }

    /**
     * Import countries from production data file.
     */
    protected function importCountries(): void
    {
        $countriesPath = database_path('seeders/data/countries.json');

        if (! File::exists($countriesPath)) {
            $this->command->error('Countries data file not found.');

            return;
        }

        $countries = json_decode(File::get($countriesPath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error('Invalid JSON in countries data file.');

            return;
        }

        foreach ($countries as $country) {
            Country::updateOrCreate(
                ['id' => $country['id']],
                $country
            );
        }

        $this->command->info('Imported '.count($countries).' countries.');
    }

    /**
     * Import languages from production data file.
     */
    protected function importLanguages(): void
    {
        $languagesPath = database_path('seeders/data/languages.json');

        if (! File::exists($languagesPath)) {
            $this->command->error('Languages data file not found.');

            return;
        }

        $languages = json_decode(File::get($languagesPath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->command->error('Invalid JSON in languages data file.');

            return;
        }

        foreach ($languages as $language) {
            Language::updateOrCreate(
                ['id' => $language['id']],
                $language
            );
        }

        $this->command->info('Imported '.count($languages).' languages.');

        // Ensure exactly one language is marked as default
        $this->ensureSingleDefaultLanguage();
    }

    /**
     * Ensure exactly one language is marked as default.
     */
    protected function ensureSingleDefaultLanguage(): void
    {
        $defaultLanguages = Language::where('is_default', true)->get();

        if ($defaultLanguages->count() === 1) {
            $this->command->info('Default language is properly set.');

            return;
        }

        if ($defaultLanguages->count() === 0) {
            // No default language, set English as default
            $english = Language::find('eng');
            if ($english) {
                $english->setDefault();
                $this->command->info('Set English as default language.');
            } else {
                $this->command->warn('English language not found, no default language set.');
            }
        } else {
            // Multiple default languages, keep only English
            Language::query()->update(['is_default' => false]);
            $english = Language::find('eng');
            if ($english) {
                $english->update(['is_default' => true]);
                $this->command->info('Reset default language to English only.');
            }
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\AuthSnapshots\AuthSnapshotFile;
use Illuminate\Console\Command;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use JsonException;
use Spatie\Permission\PermissionRegistrar;

class RestoreAuthSnapshot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auth:restore
                            {path : Filesystem path to the encrypted snapshot}
                            {--force : Replace existing user accounts and user-owned auth records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Restore user accounts, MFA setup, role assignments, direct permissions, and API tokens from an encrypted auth snapshot.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $path = AuthSnapshotFile::resolvePath((string) $this->argument('path'));

        if (! File::isFile($path)) {
            $this->error("Auth snapshot was not found at [{$path}].");

            return Command::FAILURE;
        }

        if (User::query()->exists() && ! $this->option('force')) {
            $this->error('Users already exist. Re-run with --force to replace existing user accounts and user-owned auth records.');

            return Command::FAILURE;
        }

        $payload = $this->readSnapshot($path);

        if ($payload === null) {
            return Command::FAILURE;
        }

        if (($payload['version'] ?? null) !== 1) {
            $this->error('Unsupported auth snapshot version.');

            return Command::FAILURE;
        }

        $tables = $payload['tables'] ?? [];

        try {
            DB::transaction(function () use ($tables): void {
                Schema::disableForeignKeyConstraints();

                try {
                    $this->clearUserAuthRecords();
                    $this->restoreRows('users', Arr::get($tables, 'users', []));
                    $this->restoreRoleAssignments(Arr::get($tables, 'role_assignments', []));
                    $this->restorePermissionAssignments(Arr::get($tables, 'permission_assignments', []));
                    $this->restoreRows('personal_access_tokens', Arr::get($tables, 'personal_access_tokens', []));
                } finally {
                    Schema::enableForeignKeyConstraints();
                }
            });
        } catch (\Throwable $exception) {
            $this->error('Unable to restore auth snapshot: '.$exception->getMessage());

            return Command::FAILURE;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->info('Auth snapshot restored successfully.');
        $this->line('Restored users: '.count(Arr::get($tables, 'users', [])));
        $this->line('Restored role assignments: '.count(Arr::get($tables, 'role_assignments', [])));
        $this->line('Restored direct permission assignments: '.count(Arr::get($tables, 'permission_assignments', [])));
        $this->line('Restored personal access tokens: '.count(Arr::get($tables, 'personal_access_tokens', [])));

        return Command::SUCCESS;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function readSnapshot(string $path): ?array
    {
        try {
            $contents = File::get($path);
            $decrypted = Crypt::decryptString($contents);

            return json_decode($decrypted, true, 512, JSON_THROW_ON_ERROR);
        } catch (DecryptException) {
            $this->error('Unable to decrypt auth snapshot. Verify that this app uses the same APP_KEY that created the snapshot.');
        } catch (JsonException $exception) {
            $this->error('Unable to decode auth snapshot: '.$exception->getMessage());
        } catch (\Throwable $exception) {
            $this->error('Unable to read auth snapshot: '.$exception->getMessage());
        }

        return null;
    }

    protected function clearUserAuthRecords(): void
    {
        $tables = config('permission.table_names');

        $this->deleteUserModelRows($tables['model_has_roles']);
        $this->deleteUserModelRows($tables['model_has_permissions']);

        if (Schema::hasTable('personal_access_tokens')) {
            DB::table('personal_access_tokens')
                ->where('tokenable_type', User::class)
                ->delete();
        }

        User::query()->delete();
    }

    protected function deleteUserModelRows(string $table): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        DB::table($table)
            ->where('model_type', User::class)
            ->delete();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    protected function restoreRows(string $table, array $rows): void
    {
        if ($rows === [] || ! Schema::hasTable($table)) {
            return;
        }

        $columns = Schema::getColumnListing($table);

        foreach ($rows as $row) {
            DB::table($table)->insert(Arr::only($row, $columns));
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $assignments
     */
    protected function restoreRoleAssignments(array $assignments): void
    {
        if ($assignments === []) {
            return;
        }

        $tables = config('permission.table_names');
        $columns = config('permission.column_names');
        $modelKey = $columns['model_morph_key'] ?? 'model_id';
        $roleKey = $columns['role_pivot_key'] ?? 'role_id';

        foreach ($assignments as $assignment) {
            $role = DB::table($tables['roles'])
                ->where('name', $assignment['name'])
                ->where('guard_name', $assignment['guard_name'])
                ->first();

            if ($role === null) {
                throw new \RuntimeException("Role [{$assignment['name']}] does not exist. Run php artisan permissions:sync before restoring the snapshot.");
            }

            DB::table($tables['model_has_roles'])->insert([
                $roleKey => $role->id,
                'model_type' => $assignment['model_type'],
                $modelKey => $assignment['model_id'],
            ]);
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $assignments
     */
    protected function restorePermissionAssignments(array $assignments): void
    {
        if ($assignments === []) {
            return;
        }

        $tables = config('permission.table_names');
        $columns = config('permission.column_names');
        $modelKey = $columns['model_morph_key'] ?? 'model_id';
        $permissionKey = $columns['permission_pivot_key'] ?? 'permission_id';

        foreach ($assignments as $assignment) {
            $permission = DB::table($tables['permissions'])
                ->where('name', $assignment['name'])
                ->where('guard_name', $assignment['guard_name'])
                ->first();

            if ($permission === null) {
                throw new \RuntimeException("Permission [{$assignment['name']}] does not exist. Run php artisan permissions:sync before restoring the snapshot.");
            }

            DB::table($tables['model_has_permissions'])->insert([
                $permissionKey => $permission->id,
                'model_type' => $assignment['model_type'],
                $modelKey => $assignment['model_id'],
            ]);
        }
    }
}

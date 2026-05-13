<?php

namespace Tests\Console;

use App\Enums\Permission as PermissionEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AuthSnapshotCommandTest extends TestCase
{
    use RefreshDatabase;

    private string $snapshotDirectory;

    protected function setUp(): void
    {
        parent::setUp();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->snapshotDirectory = sys_get_temp_dir().DIRECTORY_SEPARATOR.'auth_snapshot_test_'.uniqid('', true);
        File::ensureDirectoryExists($this->snapshotDirectory);
    }

    protected function tearDown(): void
    {
        if (isset($this->snapshotDirectory) && File::isDirectory($this->snapshotDirectory)) {
            File::deleteDirectory($this->snapshotDirectory);
        }

        parent::tearDown();
    }

    public function test_snapshot_is_encrypted_and_does_not_contain_app_key_or_plaintext_user_data(): void
    {
        User::factory()->create([
            'email' => 'secure-user@example.com',
            'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP'),
            'two_factor_recovery_codes' => encrypt(json_encode(['alpha-code'])),
            'two_factor_confirmed_at' => now(),
        ]);

        $path = $this->snapshotPath('test.json.enc');

        $this->artisan('auth:snapshot', [
            'path' => $path,
        ])->assertExitCode(0);

        $this->assertTrue(File::exists($path));

        $contents = File::get($path);

        $this->assertStringNotContainsString(config('app.key'), $contents);
        $this->assertStringNotContainsString('secure-user@example.com', $contents);
        $this->assertStringNotContainsString('JBSWY3DPEHPK3PXP', $contents);
        $this->assertNotSame('', $contents);
    }

    public function test_restore_recreates_users_mfa_roles_direct_permissions_and_tokens(): void
    {
        $role = Role::firstOrCreate(['name' => 'Snapshot Role', 'guard_name' => 'web']);
        $permission = Permission::firstOrCreate(['name' => PermissionEnum::ACCESS_ADMIN_PANEL->value, 'guard_name' => 'web']);
        $user = User::factory()->create([
            'email' => 'restore-user@example.com',
            'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP'),
            'two_factor_recovery_codes' => encrypt(json_encode(['alpha-code', 'beta-code'])),
            'two_factor_confirmed_at' => now(),
        ]);

        $user->assignRole($role);
        $user->givePermissionTo($permission);
        $token = $user->createToken('Snapshot token', ['view-data'])->accessToken;
        $path = $this->snapshotPath('test.json.enc');

        $this->artisan('auth:snapshot', [
            'path' => $path,
        ])->assertExitCode(0);

        DB::table('model_has_roles')->where('model_type', User::class)->delete();
        DB::table('model_has_permissions')->where('model_type', User::class)->delete();
        PersonalAccessToken::query()->delete();
        User::query()->delete();

        $this->assertDatabaseMissing('users', ['email' => 'restore-user@example.com']);

        $this->artisan('auth:restore', [
            'path' => $path,
        ])->assertExitCode(0);

        $restoredUser = User::where('email', 'restore-user@example.com')->firstOrFail();

        $this->assertSame($user->id, $restoredUser->id);
        $this->assertTrue($restoredUser->hasEnabledTwoFactorAuthentication());
        $this->assertSame('JBSWY3DPEHPK3PXP', decrypt($restoredUser->two_factor_secret));
        $this->assertTrue($restoredUser->hasRole('Snapshot Role'));
        $this->assertTrue($restoredUser->hasDirectPermission(PermissionEnum::ACCESS_ADMIN_PANEL->value));
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $token->id,
            'tokenable_type' => User::class,
            'tokenable_id' => $restoredUser->id,
            'name' => 'Snapshot token',
        ]);
    }

    public function test_restore_requires_force_when_users_already_exist(): void
    {
        User::factory()->create(['email' => 'snapshot-user@example.com']);
        $path = $this->snapshotPath('test.json.enc');

        $this->artisan('auth:snapshot', [
            'path' => $path,
        ])->assertExitCode(0);

        $this->artisan('auth:restore', [
            'path' => $path,
        ])
            ->expectsOutput('Users already exist. Re-run with --force to replace existing user accounts and user-owned auth records.')
            ->assertExitCode(1);
    }

    public function test_restore_with_force_replaces_existing_user_accounts(): void
    {
        User::factory()->create(['email' => 'snapshot-user@example.com']);
        $path = $this->snapshotPath('test.json.enc');

        $this->artisan('auth:snapshot', [
            'path' => $path,
        ])->assertExitCode(0);

        User::factory()->create(['email' => 'seeded-user@example.com']);

        $this->artisan('auth:restore', [
            'path' => $path,
            '--force' => true,
        ])->assertExitCode(0);

        $this->assertDatabaseHas('users', ['email' => 'snapshot-user@example.com']);
        $this->assertDatabaseMissing('users', ['email' => 'seeded-user@example.com']);
    }

    public function test_restore_fails_when_snapshot_cannot_be_decrypted(): void
    {
        $path = $this->snapshotPath('invalid.json.enc');

        File::put($path, 'not-encrypted');

        $this->artisan('auth:restore', [
            'path' => $path,
        ])
            ->expectsOutput('Unable to decrypt auth snapshot. Verify that this app uses the same APP_KEY that created the snapshot.')
            ->assertExitCode(1);
    }

    public function test_snapshot_creates_parent_directory_for_absolute_path(): void
    {
        User::factory()->create(['email' => 'nested-snapshot@example.com']);

        $path = $this->snapshotDirectory.DIRECTORY_SEPARATOR.'nested'.DIRECTORY_SEPARATOR.'pre-import.json.enc';

        $this->artisan('auth:snapshot', [
            'path' => $path,
        ])->assertExitCode(0);

        $this->assertTrue(File::exists($path));
    }

    public function test_snapshot_resolves_relative_path_from_current_working_directory(): void
    {
        User::factory()->create(['email' => 'relative-snapshot@example.com']);

        $originalDirectory = getcwd();

        try {
            chdir($this->snapshotDirectory);

            $this->artisan('auth:snapshot', [
                'path' => 'relative/pre-import.json.enc',
            ])->assertExitCode(0);
        } finally {
            chdir($originalDirectory);
        }

        $this->assertTrue(File::exists($this->snapshotPath('relative'.DIRECTORY_SEPARATOR.'pre-import.json.enc')));
    }

    private function snapshotPath(string $filename): string
    {
        return $this->snapshotDirectory.DIRECTORY_SEPARATOR.$filename;
    }
}

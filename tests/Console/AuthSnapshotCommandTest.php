<?php

namespace Tests\Console;

use App\Enums\Permission as PermissionEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\PersonalAccessToken;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AuthSnapshotCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function test_snapshot_is_encrypted_and_does_not_contain_app_key_or_plaintext_user_data(): void
    {
        Storage::fake('local');

        User::factory()->create([
            'email' => 'secure-user@example.com',
            'two_factor_secret' => encrypt('JBSWY3DPEHPK3PXP'),
            'two_factor_recovery_codes' => encrypt(json_encode(['alpha-code'])),
            'two_factor_confirmed_at' => now(),
        ]);

        $this->artisan('auth:snapshot', [
            'path' => 'auth-snapshots/test.json.enc',
        ])->assertExitCode(0);

        Storage::disk('local')->assertExists('auth-snapshots/test.json.enc');

        $contents = Storage::disk('local')->get('auth-snapshots/test.json.enc');

        $this->assertStringNotContainsString(config('app.key'), $contents);
        $this->assertStringNotContainsString('secure-user@example.com', $contents);
        $this->assertStringNotContainsString('JBSWY3DPEHPK3PXP', $contents);
        $this->assertNotSame('', $contents);
    }

    public function test_restore_recreates_users_mfa_roles_direct_permissions_and_tokens(): void
    {
        Storage::fake('local');

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

        $this->artisan('auth:snapshot', [
            'path' => 'auth-snapshots/test.json.enc',
        ])->assertExitCode(0);

        DB::table('model_has_roles')->where('model_type', User::class)->delete();
        DB::table('model_has_permissions')->where('model_type', User::class)->delete();
        PersonalAccessToken::query()->delete();
        User::query()->delete();

        $this->assertDatabaseMissing('users', ['email' => 'restore-user@example.com']);

        $this->artisan('auth:restore', [
            'path' => 'auth-snapshots/test.json.enc',
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
        Storage::fake('local');

        User::factory()->create(['email' => 'snapshot-user@example.com']);

        $this->artisan('auth:snapshot', [
            'path' => 'auth-snapshots/test.json.enc',
        ])->assertExitCode(0);

        $this->artisan('auth:restore', [
            'path' => 'auth-snapshots/test.json.enc',
        ])
            ->expectsOutput('Users already exist. Re-run with --force to replace existing user accounts and user-owned auth records.')
            ->assertExitCode(1);
    }

    public function test_restore_with_force_replaces_existing_user_accounts(): void
    {
        Storage::fake('local');

        User::factory()->create(['email' => 'snapshot-user@example.com']);

        $this->artisan('auth:snapshot', [
            'path' => 'auth-snapshots/test.json.enc',
        ])->assertExitCode(0);

        User::factory()->create(['email' => 'seeded-user@example.com']);

        $this->artisan('auth:restore', [
            'path' => 'auth-snapshots/test.json.enc',
            '--force' => true,
        ])->assertExitCode(0);

        $this->assertDatabaseHas('users', ['email' => 'snapshot-user@example.com']);
        $this->assertDatabaseMissing('users', ['email' => 'seeded-user@example.com']);
    }

    public function test_restore_fails_when_snapshot_cannot_be_decrypted(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('auth-snapshots/invalid.json.enc', 'not-encrypted');

        $this->artisan('auth:restore', [
            'path' => 'auth-snapshots/invalid.json.enc',
        ])
            ->expectsOutput('Unable to decrypt auth snapshot. Verify that this app uses the same APP_KEY that created the snapshot.')
            ->assertExitCode(1);
    }
}

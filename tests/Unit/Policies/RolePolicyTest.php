<?php

namespace Tests\Unit\Policies;

use App\Enums\Permission;
use App\Models\User;
use App\Policies\RolePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePolicyTest extends TestCase
{
    use RefreshDatabase;

    private RolePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new RolePolicy;
    }

    private function userWithPermissions(string ...$permissions): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo($permissions);

        return $user;
    }

    private function role(): Role
    {
        return Role::firstOrCreate(['name' => 'Test Role', 'guard_name' => 'web']);
    }

    // ── viewAny ───────────────────────────────────────────────────────────────

    public function test_view_any_returns_true_for_user_with_manage_roles(): void
    {
        $user = $this->userWithPermissions(Permission::MANAGE_ROLES->value);

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_view_any_returns_false_for_user_without_manage_roles(): void
    {
        $user = $this->userWithPermissions(Permission::MANAGE_USERS->value);

        $this->assertFalse($this->policy->viewAny($user));
    }

    // ── view ──────────────────────────────────────────────────────────────────

    public function test_view_returns_true_for_user_with_manage_roles(): void
    {
        $user = $this->userWithPermissions(Permission::MANAGE_ROLES->value);

        $this->assertTrue($this->policy->view($user, $this->role()));
    }

    // ── create ────────────────────────────────────────────────────────────────

    public function test_create_returns_true_for_user_with_manage_roles(): void
    {
        $user = $this->userWithPermissions(Permission::MANAGE_ROLES->value);

        $this->assertTrue($this->policy->create($user));
    }

    public function test_create_returns_false_for_user_without_manage_roles(): void
    {
        $user = $this->userWithPermissions(Permission::MANAGE_USERS->value);

        $this->assertFalse($this->policy->create($user));
    }

    // ── update ────────────────────────────────────────────────────────────────

    public function test_update_returns_true_for_user_with_manage_roles(): void
    {
        $user = $this->userWithPermissions(Permission::MANAGE_ROLES->value);

        $this->assertTrue($this->policy->update($user, $this->role()));
    }

    // ── delete ────────────────────────────────────────────────────────────────

    public function test_delete_returns_true_for_user_with_manage_roles(): void
    {
        $user = $this->userWithPermissions(Permission::MANAGE_ROLES->value);

        $this->assertTrue($this->policy->delete($user, $this->role()));
    }

    public function test_delete_returns_false_for_user_without_manage_roles(): void
    {
        $user = $this->userWithPermissions(Permission::MANAGE_USERS->value);

        $this->assertFalse($this->policy->delete($user, $this->role()));
    }

    // ── deleteAny ─────────────────────────────────────────────────────────────

    public function test_delete_any_returns_true_for_user_with_manage_roles(): void
    {
        $user = $this->userWithPermissions(Permission::MANAGE_ROLES->value);

        $this->assertTrue($this->policy->deleteAny($user));
    }

    public function test_delete_any_returns_false_for_user_without_manage_roles(): void
    {
        $user = $this->userWithPermissions(Permission::MANAGE_USERS->value);

        $this->assertFalse($this->policy->deleteAny($user));
    }
}

<?php

namespace Tests\Unit\Policies;

use App\Enums\Permission;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    private UserPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new UserPolicy;
    }

    private function userWithPermissions(string ...$permissions): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo($permissions);

        return $user;
    }

    // ── viewAny ───────────────────────────────────────────────────────────────

    public function test_view_any_returns_true_for_user_with_manage_users(): void
    {
        $user = $this->userWithPermissions(Permission::MANAGE_USERS->value);

        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_view_any_returns_false_for_user_without_manage_users(): void
    {
        $user = $this->userWithPermissions(Permission::VIEW_DATA->value);

        $this->assertFalse($this->policy->viewAny($user));
    }

    // ── view ──────────────────────────────────────────────────────────────────

    public function test_view_returns_true_for_user_with_manage_users(): void
    {
        $actor = $this->userWithPermissions(Permission::MANAGE_USERS->value);
        $target = User::factory()->create();

        $this->assertTrue($this->policy->view($actor, $target));
    }

    // ── create ────────────────────────────────────────────────────────────────

    public function test_create_returns_true_for_user_with_manage_users(): void
    {
        $user = $this->userWithPermissions(Permission::MANAGE_USERS->value);

        $this->assertTrue($this->policy->create($user));
    }

    public function test_create_returns_false_for_user_without_manage_users(): void
    {
        $user = $this->userWithPermissions(Permission::VIEW_DATA->value);

        $this->assertFalse($this->policy->create($user));
    }

    // ── update ────────────────────────────────────────────────────────────────

    public function test_update_returns_true_for_user_with_manage_users(): void
    {
        $actor = $this->userWithPermissions(Permission::MANAGE_USERS->value);
        $target = User::factory()->create();

        $this->assertTrue($this->policy->update($actor, $target));
    }

    // ── delete ────────────────────────────────────────────────────────────────

    public function test_delete_returns_true_for_user_with_assign_roles_deleting_another_user(): void
    {
        $actor = $this->userWithPermissions(Permission::ASSIGN_ROLES->value);
        $target = User::factory()->create();

        $this->assertTrue($this->policy->delete($actor, $target));
    }

    public function test_delete_returns_false_for_user_trying_to_delete_themselves(): void
    {
        $user = $this->userWithPermissions(Permission::ASSIGN_ROLES->value);

        $this->assertFalse($this->policy->delete($user, $user));
    }

    public function test_delete_returns_false_for_user_without_assign_roles(): void
    {
        $actor = $this->userWithPermissions(Permission::MANAGE_USERS->value);
        $target = User::factory()->create();

        $this->assertFalse($this->policy->delete($actor, $target));
    }

    // ── deleteAny ─────────────────────────────────────────────────────────────

    public function test_delete_any_returns_true_for_user_with_assign_roles(): void
    {
        $user = $this->userWithPermissions(Permission::ASSIGN_ROLES->value);

        $this->assertTrue($this->policy->deleteAny($user));
    }

    public function test_delete_any_returns_false_for_user_without_assign_roles(): void
    {
        $user = $this->userWithPermissions(Permission::MANAGE_USERS->value);

        $this->assertFalse($this->policy->deleteAny($user));
    }

    // ── approve ───────────────────────────────────────────────────────────────

    public function test_approve_returns_true_for_user_with_manage_users(): void
    {
        $actor = $this->userWithPermissions(Permission::MANAGE_USERS->value);
        $target = User::factory()->create();

        $this->assertTrue($this->policy->approve($actor, $target));
    }

    public function test_approve_returns_false_for_user_without_manage_users(): void
    {
        $actor = $this->userWithPermissions(Permission::VIEW_DATA->value);
        $target = User::factory()->create();

        $this->assertFalse($this->policy->approve($actor, $target));
    }

    // ── suspend ───────────────────────────────────────────────────────────────

    public function test_suspend_returns_true_for_user_with_manage_users_on_another_user(): void
    {
        $actor = $this->userWithPermissions(Permission::MANAGE_USERS->value);
        $target = User::factory()->create();

        $this->assertTrue($this->policy->suspend($actor, $target));
    }

    public function test_suspend_returns_false_when_suspending_oneself(): void
    {
        $user = $this->userWithPermissions(Permission::MANAGE_USERS->value);

        $this->assertFalse($this->policy->suspend($user, $user));
    }

    public function test_suspend_returns_false_for_user_without_manage_users(): void
    {
        $actor = $this->userWithPermissions(Permission::VIEW_DATA->value);
        $target = User::factory()->create();

        $this->assertFalse($this->policy->suspend($actor, $target));
    }
}

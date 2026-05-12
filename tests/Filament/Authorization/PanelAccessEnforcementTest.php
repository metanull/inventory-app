<?php

namespace Tests\Filament\Authorization;

use App\Enums\Permission;
use App\Filament\Auth\Login as AdminLogin;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PanelAccessEnforcementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    protected function createFullyEligibleUser(): User
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'approved_at' => now(),
            'suspended_at' => null,
        ]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        return $user;
    }

    // ── canAccessPanel: email verification ────────────────────────────────────

    public function test_unverified_user_with_permission_cannot_access_panel(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
            'approved_at' => now(),
        ]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        /** @var Panel $panel */
        $panel = Filament::getPanel('admin');
        $this->assertFalse($user->canAccessPanel($panel));
    }

    public function test_unverified_user_gets_forbidden_on_dashboard(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
            'approved_at' => now(),
        ]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_login_rejects_unverified_user_before_2fa(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
            'approved_at' => now(),
        ]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        Livewire::test(AdminLogin::class)
            ->set('data.email', $user->email)
            ->set('data.password', 'password')
            ->call('authenticate')
            ->assertHasErrors(['data.email']);

        $this->assertGuest();
    }

    // ── canAccessPanel: approval ───────────────────────────────────────────────

    public function test_unapproved_user_with_permission_cannot_access_panel(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'approved_at' => null,
        ]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        /** @var Panel $panel */
        $panel = Filament::getPanel('admin');
        $this->assertFalse($user->canAccessPanel($panel));
    }

    public function test_unapproved_user_gets_forbidden_on_dashboard(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'approved_at' => null,
        ]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_login_rejects_unapproved_user_before_2fa(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'approved_at' => null,
        ]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        Livewire::test(AdminLogin::class)
            ->set('data.email', $user->email)
            ->set('data.password', 'password')
            ->call('authenticate')
            ->assertHasErrors(['data.email']);

        $this->assertGuest();
    }

    // ── canAccessPanel: suspension ────────────────────────────────────────────

    public function test_suspended_user_with_permission_cannot_access_panel(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'approved_at' => now(),
            'suspended_at' => now(),
        ]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        /** @var Panel $panel */
        $panel = Filament::getPanel('admin');
        $this->assertFalse($user->canAccessPanel($panel));
    }

    public function test_suspended_user_gets_forbidden_on_dashboard(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'approved_at' => now(),
            'suspended_at' => now(),
        ]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_login_rejects_suspended_user_before_2fa(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'approved_at' => now(),
            'suspended_at' => now(),
        ]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        Livewire::test(AdminLogin::class)
            ->set('data.email', $user->email)
            ->set('data.password', 'password')
            ->call('authenticate')
            ->assertHasErrors(['data.email']);

        $this->assertGuest();
    }

    // ── canAccessPanel: permission ─────────────────────────────────────────────

    public function test_user_without_permission_cannot_access_panel_even_if_verified_and_approved(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'approved_at' => now(),
            'suspended_at' => null,
        ]);

        /** @var Panel $panel */
        $panel = Filament::getPanel('admin');
        $this->assertFalse($user->canAccessPanel($panel));
    }

    // ── canAccessPanel: fully eligible ────────────────────────────────────────

    public function test_fully_eligible_user_can_access_panel(): void
    {
        $user = $this->createFullyEligibleUser();

        /** @var Panel $panel */
        $panel = Filament::getPanel('admin');
        $this->assertTrue($user->canAccessPanel($panel));
    }

    public function test_fully_eligible_user_can_access_dashboard(): void
    {
        $user = $this->createFullyEligibleUser();

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertSee('Dashboard');
    }
}

<?php

namespace Tests\Filament\Widgets;

use App\Enums\Permission;
use App\Filament\Widgets\PendingRegistrationsWidget;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PendingRegistrationsWidgetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_widget_is_visible_only_for_user_with_manage_users_permission(): void
    {
        $user = $this->createManagerUser();
        $this->actingAs($user);

        $this->assertTrue(PendingRegistrationsWidget::canView());
    }

    public function test_widget_is_not_visible_for_user_with_only_view_data_permission(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::VIEW_DATA->value,
        ]);
        $this->actingAs($user);

        $this->assertFalse(PendingRegistrationsWidget::canView());
    }

    public function test_widget_is_not_visible_for_unauthenticated_user(): void
    {
        $this->assertFalse(PendingRegistrationsWidget::canView());
    }

    public function test_widget_renders_successfully_for_manager(): void
    {
        $user = $this->createManagerUser();

        Livewire::actingAs($user)
            ->test(PendingRegistrationsWidget::class)
            ->assertSuccessful();
    }

    public function test_widget_shows_pending_users(): void
    {
        $manager = $this->createManagerUser();
        $pendingUser = User::factory()->create([
            'email_verified_at' => now(),
            'approved_at' => null,
        ]);

        Livewire::actingAs($manager)
            ->test(PendingRegistrationsWidget::class)
            ->assertCanSeeTableRecords([$pendingUser]);
    }

    public function test_widget_does_not_show_approved_users(): void
    {
        $manager = $this->createManagerUser();
        $approvedUser = User::factory()->create([
            'email_verified_at' => now(),
            'approved_at' => now(),
        ]);

        Livewire::actingAs($manager)
            ->test(PendingRegistrationsWidget::class)
            ->assertCanNotSeeTableRecords([$approvedUser]);
    }

    public function test_widget_shows_empty_state_when_no_pending_users(): void
    {
        $manager = $this->createManagerUser();

        Livewire::actingAs($manager)
            ->test(PendingRegistrationsWidget::class)
            ->assertSee('No pending registrations');
    }

    public function test_widget_limits_to_ten_pending_users(): void
    {
        $manager = $this->createManagerUser();
        User::factory()->count(15)->create([
            'email_verified_at' => now(),
            'approved_at' => null,
        ]);

        $component = Livewire::actingAs($manager)
            ->test(PendingRegistrationsWidget::class);

        $component->assertSuccessful();

        $this->assertLessThanOrEqual(10, $component->instance()->getTableRecords()->count());
    }

    public function test_approve_action_sets_approved_at(): void
    {
        $manager = $this->createManagerUser();
        $pendingUser = User::factory()->create([
            'email_verified_at' => now(),
            'approved_at' => null,
        ]);

        Livewire::actingAs($manager)
            ->test(PendingRegistrationsWidget::class)
            ->callTableAction('approve', $pendingUser)
            ->assertHasNoTableActionErrors();

        $this->assertNotNull($pendingUser->fresh()->approved_at);
    }

    protected function createManagerUser(): User
    {
        $user = User::factory()->create(['email_verified_at' => now(), 'approved_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::MANAGE_USERS->value,
        ]);

        return $user;
    }
}

<?php

namespace Tests\Filament\Authorization;

use App\Enums\Permission;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartnerResourceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_without_view_data_permission_cannot_see_partner_navigation_or_pages(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $dashboard = $this->actingAs($user)->get('/admin');

        $dashboard
            ->assertOk()
            ->assertDontSee('Partners');

        $this->actingAs($user)->get('/admin/partners')
            ->assertForbidden();
    }

    public function test_view_only_users_can_open_partner_index_and_view_but_not_create_or_edit(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::VIEW_DATA->value,
        ]);

        $partner = Partner::factory()->create(['internal_name' => 'Jordan Museum']);

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertSee('Partners');

        $this->actingAs($user)->get('/admin/partners')
            ->assertOk()
            ->assertSee('Jordan Museum');

        $this->actingAs($user)->get("/admin/partners/{$partner->getKey()}")
            ->assertOk()
            ->assertSee('Jordan Museum');

        $this->actingAs($user)->get('/admin/partners/create')
            ->assertForbidden();

        $this->actingAs($user)->get("/admin/partners/{$partner->getKey()}/edit")
            ->assertForbidden();
    }
}

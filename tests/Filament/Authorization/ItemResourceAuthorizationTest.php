<?php

namespace Tests\Filament\Authorization;

use App\Enums\Permission;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemResourceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_without_view_data_permission_cannot_see_item_navigation_or_pages(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $dashboard = $this->actingAs($user)->get('/admin');

        $dashboard
            ->assertOk()
            ->assertDontSee('Items');

        $this->actingAs($user)->get('/admin/items')
            ->assertForbidden();
    }

    public function test_view_only_users_can_open_item_index_and_view_but_not_create_or_edit(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::VIEW_DATA->value,
        ]);

        $item = Item::factory()->Object()->create(['internal_name' => 'Temple relief']);

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertSee('Items');

        $this->actingAs($user)->get('/admin/items')
            ->assertOk()
            ->assertSee('Temple relief');

        $this->actingAs($user)->get("/admin/items/{$item->getKey()}")
            ->assertOk()
            ->assertSee('Temple relief');

        $this->actingAs($user)->get('/admin/items/create')
            ->assertForbidden();

        $this->actingAs($user)->get("/admin/items/{$item->getKey()}/edit")
            ->assertForbidden();
    }

    public function test_browse_item_tree_page_requires_view_data_permission(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertDontSee('Browse item tree');

        $this->actingAs($user)->get('/admin/browse-item-tree')
            ->assertForbidden();
    }

    public function test_browse_item_tree_page_is_accessible_with_view_data_permission(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::VIEW_DATA->value,
        ]);

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertSee('Browse item tree');

        $this->actingAs($user)->get('/admin/browse-item-tree')
            ->assertOk();
    }
}

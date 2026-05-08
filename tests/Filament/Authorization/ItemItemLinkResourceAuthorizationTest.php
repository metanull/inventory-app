<?php

namespace Tests\Filament\Authorization;

use App\Enums\Permission;
use App\Models\Context;
use App\Models\Item;
use App\Models\ItemItemLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemItemLinkResourceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_without_view_data_permission_cannot_see_item_item_link_navigation_or_pages(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertDontSee('Item Links');

        $this->actingAs($user)->get('/admin/item-item-links')
            ->assertForbidden();
    }

    public function test_view_only_users_can_open_item_item_link_index_and_view_but_not_create_or_edit(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::VIEW_DATA->value,
        ]);

        $source = Item::factory()->Object()->create(['internal_name' => 'Source item']);
        $target = Item::factory()->Object()->create(['internal_name' => 'Target item']);
        $link = ItemItemLink::factory()->create([
            'source_id' => $source->id,
            'target_id' => $target->id,
        ]);

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertSee('Item Links');

        $this->actingAs($user)->get('/admin/item-item-links')
            ->assertOk();

        $this->actingAs($user)->get("/admin/item-item-links/{$link->getKey()}")
            ->assertOk();

        $this->actingAs($user)->get('/admin/item-item-links/create')
            ->assertForbidden();

        $this->actingAs($user)->get("/admin/item-item-links/{$link->getKey()}/edit")
            ->assertForbidden();
    }

    public function test_users_without_admin_panel_permission_receive_forbidden_on_item_item_link_pages(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::VIEW_DATA->value,
            Permission::CREATE_DATA->value,
            Permission::UPDATE_DATA->value,
            Permission::DELETE_DATA->value,
        ]);

        $this->actingAs($user)->get('/admin/item-item-links')->assertForbidden();
    }

    public function test_crud_user_can_create_edit_and_delete_item_item_links(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::VIEW_DATA->value,
            Permission::CREATE_DATA->value,
            Permission::UPDATE_DATA->value,
            Permission::DELETE_DATA->value,
        ]);

        $source = Item::factory()->Object()->create(['internal_name' => 'Source item']);
        $target = Item::factory()->Object()->create(['internal_name' => 'Target item']);
        $context = Context::factory()->create(['internal_name' => 'Catalogue']);
        $link = ItemItemLink::factory()->create([
            'source_id' => $source->id,
            'target_id' => $target->id,
            'context_id' => $context->id,
        ]);

        $this->actingAs($user)->get('/admin/item-item-links/create')
            ->assertOk();

        $this->actingAs($user)->get("/admin/item-item-links/{$link->getKey()}/edit")
            ->assertOk();
    }
}

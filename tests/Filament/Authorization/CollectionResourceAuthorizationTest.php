<?php

namespace Tests\Filament\Authorization;

use App\Enums\Permission;
use App\Models\Collection;
use App\Models\Context;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionResourceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_without_view_data_permission_cannot_see_collection_navigation_or_pages(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $dashboard = $this->actingAs($user)->get('/admin');

        $dashboard
            ->assertOk()
            ->assertDontSee('Collections');

        $this->actingAs($user)->get('/admin/collections')
            ->assertForbidden();
    }

    public function test_view_only_users_can_open_collection_index_and_view_but_not_create_or_edit(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::VIEW_DATA->value,
        ]);

        $language = Language::factory()->create(['id' => 'eng', 'internal_name' => 'English', 'is_default' => true]);
        $context = Context::factory()->create(['internal_name' => 'Catalogue', 'is_default' => true]);
        $collection = Collection::factory()->create([
            'internal_name' => 'Temple collection',
            'context_id' => $context->id,
            'language_id' => $language->id,
        ]);

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertSee('Collections');

        $this->actingAs($user)->get('/admin/collections')
            ->assertOk()
            ->assertSee('Temple collection');

        $this->actingAs($user)->get("/admin/collections/{$collection->getKey()}")
            ->assertOk()
            ->assertSee('Temple collection');

        $this->actingAs($user)->get('/admin/collections/create')
            ->assertForbidden();

        $this->actingAs($user)->get("/admin/collections/{$collection->getKey()}/edit")
            ->assertForbidden();
    }

    public function test_browse_collection_tree_page_requires_view_data_permission(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertDontSee('Browse tree');

        $this->actingAs($user)->get('/admin/browse-collection-tree')
            ->assertForbidden();
    }

    public function test_browse_collection_tree_page_is_accessible_with_view_data_permission(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::VIEW_DATA->value,
        ]);

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertSee('Browse tree');

        $this->actingAs($user)->get('/admin/browse-collection-tree')
            ->assertOk();
    }
}

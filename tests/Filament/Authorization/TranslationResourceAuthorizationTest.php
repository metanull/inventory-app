<?php

namespace Tests\Filament\Authorization;

use App\Enums\Permission;
use App\Models\Collection;
use App\Models\CollectionTranslation;
use App\Models\Context;
use App\Models\Item;
use App\Models\ItemTranslation;
use App\Models\Language;
use App\Models\Partner;
use App\Models\PartnerTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationResourceAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    // ─── ItemTranslationResource ────────────────────────────────────────────────

    public function test_users_without_view_data_permission_cannot_see_item_translation_navigation_or_pages(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertDontSee('Item Translations');

        $this->actingAs($user)->get('/admin/item-translations')
            ->assertForbidden();
    }

    public function test_view_only_users_can_open_item_translation_index_but_not_create_or_edit(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::VIEW_DATA->value,
        ]);

        $language = Language::factory()->create(['id' => 'eng', 'internal_name' => 'English', 'is_default' => true]);
        $context = Context::factory()->create(['internal_name' => 'Catalogue', 'is_default' => true]);
        $item = Item::factory()->Object()->create(['internal_name' => 'Temple relief']);
        $translation = ItemTranslation::factory()->create([
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Relief of the temple',
        ]);

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertSee('Item Translations');

        $this->actingAs($user)->get('/admin/item-translations')
            ->assertOk()
            ->assertSee('Relief of the temple');

        $this->actingAs($user)->get('/admin/item-translations/create')
            ->assertForbidden();

        $this->actingAs($user)->get("/admin/item-translations/{$translation->getKey()}/edit")
            ->assertForbidden();
    }

    // ─── CollectionTranslationResource ─────────────────────────────────────────

    public function test_users_without_view_data_permission_cannot_see_collection_translation_navigation_or_pages(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertDontSee('Collection Translations');

        $this->actingAs($user)->get('/admin/collection-translations')
            ->assertForbidden();
    }

    public function test_view_only_users_can_open_collection_translation_index_but_not_create_or_edit(): void
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
        $translation = CollectionTranslation::factory()->create([
            'collection_id' => $collection->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'title' => 'Temple Collection EN',
        ]);

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertSee('Collection Translations');

        $this->actingAs($user)->get('/admin/collection-translations')
            ->assertOk()
            ->assertSee('Temple Collection EN');

        $this->actingAs($user)->get('/admin/collection-translations/create')
            ->assertForbidden();

        $this->actingAs($user)->get("/admin/collection-translations/{$translation->getKey()}/edit")
            ->assertForbidden();
    }

    // ─── PartnerTranslationResource ─────────────────────────────────────────────

    public function test_users_without_view_data_permission_cannot_see_partner_translation_navigation_or_pages(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo(Permission::ACCESS_ADMIN_PANEL->value);

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertDontSee('Partner Translations');

        $this->actingAs($user)->get('/admin/partner-translations')
            ->assertForbidden();
    }

    public function test_view_only_users_can_open_partner_translation_index_but_not_create_or_edit(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::VIEW_DATA->value,
        ]);

        $language = Language::factory()->create(['id' => 'eng', 'internal_name' => 'English', 'is_default' => true]);
        $context = Context::factory()->create(['internal_name' => 'Catalogue', 'is_default' => true]);
        $partner = Partner::factory()->create(['internal_name' => 'Jordan Museum']);
        $translation = PartnerTranslation::factory()->create([
            'partner_id' => $partner->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Jordan Museum EN',
        ]);

        $this->actingAs($user)->get('/admin')
            ->assertOk()
            ->assertSee('Partner Translations');

        $this->actingAs($user)->get('/admin/partner-translations')
            ->assertOk()
            ->assertSee('Jordan Museum EN');

        $this->actingAs($user)->get('/admin/partner-translations/create')
            ->assertForbidden();

        $this->actingAs($user)->get("/admin/partner-translations/{$translation->getKey()}/edit")
            ->assertForbidden();
    }

    // ─── Cross-surface isolation ────────────────────────────────────────────────

    public function test_users_without_admin_panel_permission_receive_forbidden_on_translation_resource_pages(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::VIEW_DATA->value,
            Permission::CREATE_DATA->value,
            Permission::UPDATE_DATA->value,
            Permission::DELETE_DATA->value,
        ]);

        $this->actingAs($user)->get('/admin/item-translations')->assertForbidden();
        $this->actingAs($user)->get('/admin/collection-translations')->assertForbidden();
        $this->actingAs($user)->get('/admin/partner-translations')->assertForbidden();
    }
}

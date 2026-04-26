<?php

namespace Tests\Filament\Resources;

use App\Enums\Permission;
use App\Filament\Resources\CollectionResource\Pages\CreateCollection;
use App\Filament\Resources\CollectionResource\Pages\EditCollection;
use App\Filament\Resources\CollectionResource\Pages\ListCollection;
use App\Filament\Resources\CollectionResource\Pages\ViewCollection;
use App\Filament\Resources\CollectionResource\RelationManagers\ChildCollectionsRelationManager;
use App\Filament\Resources\CollectionResource\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\CollectionResource\RelationManagers\PartnersRelationManager;
use App\Filament\Resources\CollectionResource\RelationManagers\TranslationsRelationManager;
use App\Models\Collection;
use App\Models\Context;
use App\Models\Item;
use App\Models\Language;
use App\Models\Partner;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CollectionResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_users_can_render_all_collection_resource_pages(): void
    {
        $user = $this->createCrudUser();
        $context = Context::factory()->create(['internal_name' => 'Catalogue']);
        $language = Language::factory()->create(['id' => 'eng', 'internal_name' => 'English']);
        $collection = Collection::factory()->create([
            'internal_name' => 'Temple collection',
            'type' => 'collection',
            'context_id' => $context->id,
            'language_id' => $language->id,
        ]);

        $this->actingAs($user)->get('/admin/collections')
            ->assertOk()
            ->assertSee('Collections');

        $this->actingAs($user)->get('/admin/collections/create')
            ->assertOk()
            ->assertSee('Create');

        $this->actingAs($user)->get("/admin/collections/{$collection->getKey()}/edit")
            ->assertOk()
            ->assertSee('Temple collection')
            ->assertSee('Child collections')
            ->assertSee('Items')
            ->assertSee('Partners')
            ->assertSee('Translations');

        $this->actingAs($user)->get("/admin/collections/{$collection->getKey()}")
            ->assertOk()
            ->assertSee('Temple collection');
    }

    public function test_authorized_users_can_create_edit_and_delete_collections(): void
    {
        $user = $this->createCrudUser();
        $context = Context::factory()->create(['internal_name' => 'Catalogue']);
        $language = Language::factory()->create(['id' => 'eng', 'internal_name' => 'English']);
        $collection = Collection::factory()->create([
            'internal_name' => 'Temple collection',
            'type' => 'collection',
            'backward_compatibility' => 'col-01',
            'context_id' => $context->id,
            'language_id' => $language->id,
        ]);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(CreateCollection::class)
            ->fillForm([
                'internal_name' => 'Archive collection',
                'type' => 'exhibition',
                'backward_compatibility' => 'col-02',
                'context_id' => $context->id,
                'language_id' => $language->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('collections', [
            'internal_name' => 'Archive collection',
            'type' => 'exhibition',
            'backward_compatibility' => 'col-02',
            'context_id' => $context->id,
            'language_id' => $language->id,
        ]);

        Livewire::actingAs($user)
            ->test(EditCollection::class, [
                'record' => $collection->getRouteKey(),
            ])
            ->assertFormSet([
                'internal_name' => 'Temple collection',
                'type' => 'collection',
                'backward_compatibility' => 'col-01',
                'context_id' => $context->id,
                'language_id' => $language->id,
            ])
            ->fillForm([
                'internal_name' => 'Temple migration',
                'type' => 'gallery',
                'backward_compatibility' => 'col-11',
                'context_id' => $context->id,
                'language_id' => $language->id,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('collections', [
            'id' => $collection->id,
            'internal_name' => 'Temple migration',
            'type' => 'gallery',
            'backward_compatibility' => 'col-11',
        ]);

        Livewire::actingAs($user)
            ->test(ListCollection::class)
            ->callTableAction(DeleteAction::class, $collection)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('collections', [
            'id' => $collection->id,
        ]);
    }

    public function test_authorized_users_can_change_collection_parent(): void
    {
        $user = $this->createCrudUser();
        $parent = Collection::factory()->create(['internal_name' => 'Root collection']);
        $child = Collection::factory()->create(['internal_name' => 'Child collection', 'parent_id' => null]);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(ListCollection::class)
            ->callTableAction('changeParent', $child, data: ['parent_id' => $parent->id])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('collections', [
            'id' => $child->id,
            'parent_id' => $parent->id,
        ]);
    }

    public function test_authorized_users_can_move_collections_to_parent_in_bulk(): void
    {
        $user = $this->createCrudUser();
        $parent = Collection::factory()->create(['internal_name' => 'Root collection']);
        $child1 = Collection::factory()->create(['internal_name' => 'Child 1', 'parent_id' => null]);
        $child2 = Collection::factory()->create(['internal_name' => 'Child 2', 'parent_id' => null]);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(ListCollection::class)
            ->callTableBulkAction('moveToParent', [$child1, $child2], data: ['parent_id' => $parent->id])
            ->assertHasNoTableBulkActionErrors();

        $this->assertDatabaseHas('collections', ['id' => $child1->id, 'parent_id' => $parent->id]);
        $this->assertDatabaseHas('collections', ['id' => $child2->id, 'parent_id' => $parent->id]);
    }

    public function test_child_collections_relation_manager_shows_children(): void
    {
        $user = $this->createCrudUser();
        $parent = Collection::factory()->create(['internal_name' => 'Root collection']);
        $child = Collection::factory()->create([
            'internal_name' => 'Nested collection',
            'parent_id' => $parent->id,
        ]);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(ChildCollectionsRelationManager::class, [
                'ownerRecord' => $parent,
                'pageClass' => ViewCollection::class,
            ])
            ->assertCanSeeTableRecords([$child]);
    }

    public function test_items_relation_manager_supports_attach_and_detach(): void
    {
        $user = $this->createCrudUser();
        $collection = Collection::factory()->create(['internal_name' => 'Temple collection']);
        $item = Item::factory()->Object()->create(['internal_name' => 'Temple relief']);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(ItemsRelationManager::class, [
                'ownerRecord' => $collection,
                'pageClass' => EditCollection::class,
            ])
            ->callTableAction('attach', data: ['recordId' => $item->id])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('collection_item', [
            'collection_id' => $collection->id,
            'item_id' => $item->id,
        ]);

        Livewire::actingAs($user)
            ->test(ItemsRelationManager::class, [
                'ownerRecord' => $collection,
                'pageClass' => EditCollection::class,
            ])
            ->callTableAction('detach', $item)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('collection_item', [
            'collection_id' => $collection->id,
            'item_id' => $item->id,
        ]);
    }

    public function test_partners_relation_manager_supports_attach_and_detach(): void
    {
        $user = $this->createCrudUser();
        $collection = Collection::factory()->create(['internal_name' => 'Temple collection']);
        $partner = Partner::factory()->create(['internal_name' => 'Jordan Museum']);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(PartnersRelationManager::class, [
                'ownerRecord' => $collection,
                'pageClass' => EditCollection::class,
            ])
            ->callTableAction('attach', data: [
                'recordId' => $partner->id,
                'level' => 'partner',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('collection_partner', [
            'collection_id' => $collection->id,
            'partner_id' => $partner->id,
            'level' => 'partner',
        ]);

        Livewire::actingAs($user)
            ->test(PartnersRelationManager::class, [
                'ownerRecord' => $collection,
                'pageClass' => EditCollection::class,
            ])
            ->callTableAction('detach', $partner)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('collection_partner', [
            'collection_id' => $collection->id,
            'partner_id' => $partner->id,
        ]);
    }

    public function test_translations_relation_manager_supports_crud_operations(): void
    {
        $user = $this->createCrudUser();
        $collection = Collection::factory()->create(['internal_name' => 'Temple collection']);
        $language = Language::factory()->create(['id' => 'eng', 'internal_name' => 'English']);
        $context = Context::factory()->create(['internal_name' => 'Catalogue']);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(TranslationsRelationManager::class, [
                'ownerRecord' => $collection,
                'pageClass' => EditCollection::class,
            ])
            ->mountTableAction('create')
            ->setTableActionData([
                'language_id' => $language->id,
                'context_id' => $context->id,
                'title' => 'Temple of Amman',
                'description' => 'A temple in Amman.',
            ])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $translation = $collection->translations()->firstOrFail();

        Livewire::actingAs($user)
            ->test(TranslationsRelationManager::class, [
                'ownerRecord' => $collection,
                'pageClass' => EditCollection::class,
            ])
            ->assertCanSeeTableRecords([$translation])
            ->mountTableAction(EditAction::class, $translation)
            ->setTableActionData([
                'language_id' => $language->id,
                'context_id' => $context->id,
                'title' => 'Temple of Jordan',
                'description' => 'Updated description.',
            ])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('collection_translations', [
            'id' => $translation->id,
            'title' => 'Temple of Jordan',
            'description' => 'Updated description.',
        ]);

        Livewire::actingAs($user)
            ->test(TranslationsRelationManager::class, [
                'ownerRecord' => $collection,
                'pageClass' => EditCollection::class,
            ])
            ->callTableAction(DeleteAction::class, $translation->fresh())
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('collection_translations', [
            'id' => $translation->id,
        ]);
    }

    public function test_users_without_admin_panel_permission_receive_forbidden_on_the_filament_collection_resource(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::VIEW_DATA->value,
            Permission::CREATE_DATA->value,
            Permission::UPDATE_DATA->value,
            Permission::DELETE_DATA->value,
        ]);

        $response = $this->actingAs($user)->get('/admin/collections');

        $response->assertForbidden();
    }

    protected function createCrudUser(): User
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::ACCESS_ADMIN_PANEL->value,
            Permission::VIEW_DATA->value,
            Permission::CREATE_DATA->value,
            Permission::UPDATE_DATA->value,
            Permission::DELETE_DATA->value,
        ]);

        return $user;
    }

    protected function setCurrentPanel(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }
}

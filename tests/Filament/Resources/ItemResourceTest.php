<?php

namespace Tests\Filament\Resources;

use App\Enums\ItemType;
use App\Enums\Permission;
use App\Filament\Pages\BrowseItemTree;
use App\Filament\Resources\ItemResource\Pages\CreateItem;
use App\Filament\Resources\ItemResource\Pages\EditItem;
use App\Filament\Resources\ItemResource\Pages\ListItem;
use App\Filament\Resources\ItemResource\Pages\ViewItem;
use App\Filament\Resources\ItemResource\RelationManagers\ChildItemsRelationManager;
use App\Filament\Resources\ItemResource\RelationManagers\LinksRelationManager;
use App\Filament\Resources\ItemResource\RelationManagers\PicturesRelationManager;
use App\Filament\Resources\ItemResource\RelationManagers\TagsRelationManager;
use App\Filament\Resources\ItemResource\RelationManagers\TranslationsRelationManager;
use App\Models\Collection;
use App\Models\Context;
use App\Models\Item;
use App\Models\Language;
use App\Models\Partner;
use App\Models\Tag;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ItemResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_users_can_render_all_item_resource_pages(): void
    {
        $user = $this->createCrudUser();
        $partner = Partner::factory()->create(['internal_name' => 'Jordan Museum']);
        $item = Item::factory()->Object()->create([
            'internal_name' => 'Temple relief',
            'partner_id' => $partner->id,
        ]);

        $this->actingAs($user)->get('/admin/items')
            ->assertOk()
            ->assertSee('Items');

        $this->actingAs($user)->get('/admin/items/create')
            ->assertOk()
            ->assertSee('Create');

        $this->actingAs($user)->get("/admin/items/{$item->getKey()}/edit")
            ->assertOk()
            ->assertSee('Temple relief')
            ->assertSee('Child items')
            ->assertSee('Pictures')
            ->assertSee('Translations')
            ->assertSee('Links');

        $this->actingAs($user)->get("/admin/items/{$item->getKey()}")
            ->assertOk()
            ->assertSee('Temple relief');

        $this->actingAs($user)->get('/admin/browse-item-tree')
            ->assertOk();
    }

    public function test_authorized_users_can_create_edit_and_delete_items(): void
    {
        $user = $this->createCrudUser();
        $partner = Partner::factory()->create(['internal_name' => 'Jordan Museum']);
        $item = Item::factory()->Object()->create([
            'internal_name' => 'Temple relief',
            'backward_compatibility' => 'itm-01',
            'partner_id' => $partner->id,
        ]);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(CreateItem::class)
            ->fillForm([
                'internal_name' => 'Stone tablet',
                'type' => ItemType::OBJECT->value,
                'backward_compatibility' => 'itm-02',
                'partner_id' => $partner->id,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('items', [
            'internal_name' => 'Stone tablet',
            'type' => ItemType::OBJECT->value,
            'backward_compatibility' => 'itm-02',
            'partner_id' => $partner->id,
        ]);

        Livewire::actingAs($user)
            ->test(EditItem::class, [
                'record' => $item->getRouteKey(),
            ])
            ->assertFormSet([
                'internal_name' => 'Temple relief',
                'type' => ItemType::OBJECT->value,
                'backward_compatibility' => 'itm-01',
                'partner_id' => $partner->id,
            ])
            ->fillForm([
                'internal_name' => 'Temple inscription',
                'backward_compatibility' => 'itm-11',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'internal_name' => 'Temple inscription',
            'backward_compatibility' => 'itm-11',
        ]);

        Livewire::actingAs($user)
            ->test(ListItem::class)
            ->callTableAction(DeleteAction::class, $item)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('items', [
            'id' => $item->id,
        ]);
    }

    public function test_authorized_users_can_change_item_parent(): void
    {
        $user = $this->createCrudUser();
        $parent = Item::factory()->Object()->create(['internal_name' => 'Root item']);
        $child = Item::factory()->Object()->create(['internal_name' => 'Child item', 'parent_id' => null]);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(ListItem::class)
            ->callTableAction('changeParent', $child, data: ['parent_id' => $parent->id])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('items', [
            'id' => $child->id,
            'parent_id' => $parent->id,
        ]);
    }

    public function test_authorized_users_can_move_items_to_parent_in_bulk(): void
    {
        $user = $this->createCrudUser();
        $parent = Item::factory()->Object()->create(['internal_name' => 'Root item']);
        $child1 = Item::factory()->Object()->create(['internal_name' => 'Child 1', 'parent_id' => null]);
        $child2 = Item::factory()->Object()->create(['internal_name' => 'Child 2', 'parent_id' => null]);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(ListItem::class)
            ->callTableBulkAction('moveToParent', [$child1, $child2], data: ['parent_id' => $parent->id])
            ->assertHasNoTableBulkActionErrors();

        $this->assertDatabaseHas('items', ['id' => $child1->id, 'parent_id' => $parent->id]);
        $this->assertDatabaseHas('items', ['id' => $child2->id, 'parent_id' => $parent->id]);
    }

    public function test_authorized_users_can_attach_items_to_collection_in_bulk(): void
    {
        $user = $this->createCrudUser();
        $collection = Collection::factory()->create(['internal_name' => 'Temple collection']);
        $item1 = Item::factory()->Object()->create(['internal_name' => 'Item 1']);
        $item2 = Item::factory()->Object()->create(['internal_name' => 'Item 2']);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(ListItem::class)
            ->callTableBulkAction('attachToCollection', [$item1, $item2], data: ['collection_id' => $collection->id])
            ->assertHasNoTableBulkActionErrors();

        $this->assertDatabaseHas('collection_item', ['collection_id' => $collection->id, 'item_id' => $item1->id]);
        $this->assertDatabaseHas('collection_item', ['collection_id' => $collection->id, 'item_id' => $item2->id]);
    }

    public function test_authorized_users_can_attach_tag_to_items_in_bulk(): void
    {
        $user = $this->createCrudUser();
        $tag = Tag::factory()->create(['internal_name' => 'religious']);
        $item1 = Item::factory()->Object()->create(['internal_name' => 'Item 1']);
        $item2 = Item::factory()->Object()->create(['internal_name' => 'Item 2']);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(ListItem::class)
            ->callTableBulkAction('attachTag', [$item1, $item2], data: ['tag_id' => $tag->id])
            ->assertHasNoTableBulkActionErrors();

        $this->assertDatabaseHas('item_tag', ['item_id' => $item1->id, 'tag_id' => $tag->id]);
        $this->assertDatabaseHas('item_tag', ['item_id' => $item2->id, 'tag_id' => $tag->id]);
    }

    public function test_child_items_relation_manager_shows_children(): void
    {
        $user = $this->createCrudUser();
        $parent = Item::factory()->Object()->create(['internal_name' => 'Root item']);
        $child = Item::factory()->create([
            'internal_name' => 'Detail item',
            'type' => ItemType::DETAIL,
            'parent_id' => $parent->id,
        ]);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(ChildItemsRelationManager::class, [
                'ownerRecord' => $parent,
                'pageClass' => ViewItem::class,
            ])
            ->assertCanSeeTableRecords([$child]);
    }

    public function test_pictures_relation_manager_shows_picture_children_only(): void
    {
        $user = $this->createCrudUser();
        $parent = Item::factory()->Object()->create(['internal_name' => 'Root item']);
        $picture = Item::factory()->create([
            'internal_name' => 'Picture item',
            'type' => ItemType::PICTURE,
            'parent_id' => $parent->id,
        ]);
        $detail = Item::factory()->create([
            'internal_name' => 'Detail item',
            'type' => ItemType::DETAIL,
            'parent_id' => $parent->id,
        ]);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(PicturesRelationManager::class, [
                'ownerRecord' => $parent,
                'pageClass' => ViewItem::class,
            ])
            ->assertCanSeeTableRecords([$picture])
            ->assertCanNotSeeTableRecords([$detail]);
    }

    public function test_translations_relation_manager_supports_crud_operations(): void
    {
        $user = $this->createCrudUser();
        $item = Item::factory()->Object()->create(['internal_name' => 'Temple relief']);
        $language = Language::factory()->create(['id' => 'eng', 'internal_name' => 'English']);
        $context = Context::factory()->create(['internal_name' => 'Catalogue']);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(TranslationsRelationManager::class, [
                'ownerRecord' => $item,
                'pageClass' => EditItem::class,
            ])
            ->mountTableAction('create')
            ->setTableActionData([
                'language_id' => $language->id,
                'context_id' => $context->id,
                'name' => 'Temple Relief',
                'alternate_name' => 'Relief fragment',
                'description' => 'A carved stone relief.',
            ])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $translation = $item->translations()->firstOrFail();

        Livewire::actingAs($user)
            ->test(TranslationsRelationManager::class, [
                'ownerRecord' => $item,
                'pageClass' => EditItem::class,
            ])
            ->assertCanSeeTableRecords([$translation])
            ->mountTableAction(EditAction::class, $translation)
            ->setTableActionData([
                'language_id' => $language->id,
                'context_id' => $context->id,
                'name' => 'Temple Relief Updated',
                'description' => 'Updated description.',
            ])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('item_translations', [
            'id' => $translation->id,
            'name' => 'Temple Relief Updated',
            'description' => 'Updated description.',
        ]);

        Livewire::actingAs($user)
            ->test(TranslationsRelationManager::class, [
                'ownerRecord' => $item,
                'pageClass' => EditItem::class,
            ])
            ->callTableAction(DeleteAction::class, $translation->fresh())
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('item_translations', [
            'id' => $translation->id,
        ]);
    }

    public function test_links_relation_manager_supports_crud_operations(): void
    {
        $user = $this->createCrudUser();
        $source = Item::factory()->Object()->create(['internal_name' => 'Source item']);
        $target = Item::factory()->Object()->create(['internal_name' => 'Target item']);
        $context = Context::factory()->create(['internal_name' => 'Catalogue']);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(LinksRelationManager::class, [
                'ownerRecord' => $source,
                'pageClass' => EditItem::class,
            ])
            ->mountTableAction('create')
            ->setTableActionData([
                'target_id' => $target->id,
                'context_id' => $context->id,
            ])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $link = $source->outgoingLinks()->firstOrFail();

        $this->assertDatabaseHas('item_item_links', [
            'source_id' => $source->id,
            'target_id' => $target->id,
            'context_id' => $context->id,
        ]);

        Livewire::actingAs($user)
            ->test(LinksRelationManager::class, [
                'ownerRecord' => $source,
                'pageClass' => EditItem::class,
            ])
            ->assertCanSeeTableRecords([$link])
            ->callTableAction(DeleteAction::class, $link->fresh())
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('item_item_links', [
            'id' => $link->id,
        ]);
    }

    public function test_tags_relation_manager_supports_attach_and_detach(): void
    {
        $user = $this->createCrudUser();
        $item = Item::factory()->Object()->create(['internal_name' => 'Temple relief']);
        $tag = Tag::factory()->create(['internal_name' => 'religious']);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(TagsRelationManager::class, [
                'ownerRecord' => $item,
                'pageClass' => EditItem::class,
            ])
            ->callTableAction('attach', data: ['recordId' => $tag->id])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('item_tag', [
            'item_id' => $item->id,
            'tag_id' => $tag->id,
        ]);

        Livewire::actingAs($user)
            ->test(TagsRelationManager::class, [
                'ownerRecord' => $item,
                'pageClass' => EditItem::class,
            ])
            ->callTableAction('detach', $tag)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('item_tag', [
            'item_id' => $item->id,
            'tag_id' => $tag->id,
        ]);
    }

    public function test_browse_item_tree_page_renders(): void
    {
        $user = $this->createCrudUser();
        Item::factory()->Object()->create(['internal_name' => 'Root object', 'parent_id' => null]);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(BrowseItemTree::class)
            ->assertSee('Root object');
    }

    public function test_users_without_admin_panel_permission_receive_forbidden_on_the_filament_item_resource(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->givePermissionTo([
            Permission::VIEW_DATA->value,
            Permission::CREATE_DATA->value,
            Permission::UPDATE_DATA->value,
            Permission::DELETE_DATA->value,
        ]);

        $response = $this->actingAs($user)->get('/admin/items');

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

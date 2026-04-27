<?php

namespace Tests\Filament\Resources;

use App\Enums\Permission;
use App\Filament\Resources\CollectionTranslationResource\Pages\CreateCollectionTranslation;
use App\Filament\Resources\CollectionTranslationResource\Pages\EditCollectionTranslation;
use App\Filament\Resources\CollectionTranslationResource\Pages\ListCollectionTranslation;
use App\Filament\Resources\ItemTranslationResource\Pages\CreateItemTranslation;
use App\Filament\Resources\ItemTranslationResource\Pages\EditItemTranslation;
use App\Filament\Resources\ItemTranslationResource\Pages\ListItemTranslation;
use App\Filament\Resources\PartnerTranslationResource\Pages\CreatePartnerTranslation;
use App\Filament\Resources\PartnerTranslationResource\Pages\EditPartnerTranslation;
use App\Filament\Resources\PartnerTranslationResource\Pages\ListPartnerTranslation;
use App\Models\Collection;
use App\Models\CollectionTranslation;
use App\Models\Context;
use App\Models\Item;
use App\Models\ItemTranslation;
use App\Models\Language;
use App\Models\Partner;
use App\Models\PartnerTranslation;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TranslationResourceTest extends TestCase
{
    use RefreshDatabase;

    // ─── ItemTranslation ────────────────────────────────────────────────────────

    public function test_authorized_users_can_render_all_item_translation_resource_pages(): void
    {
        $user = $this->createCrudUser();
        $language = Language::factory()->create(['id' => 'eng', 'internal_name' => 'English', 'is_default' => true]);
        $context = Context::factory()->create(['internal_name' => 'Catalogue', 'is_default' => true]);
        $item = Item::factory()->Object()->create(['internal_name' => 'Temple relief']);
        $translation = ItemTranslation::factory()->create([
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Relief of the temple',
        ]);

        $this->actingAs($user)->get('/admin/item-translations')
            ->assertOk()
            ->assertSee('Item Translations');

        $this->actingAs($user)->get('/admin/item-translations/create')
            ->assertOk()
            ->assertSee('Create');

        $this->actingAs($user)->get("/admin/item-translations/{$translation->getKey()}/edit")
            ->assertOk()
            ->assertSee('Relief of the temple');
    }

    public function test_authorized_users_can_crud_item_translations(): void
    {
        $user = $this->createCrudUser();
        $language = Language::factory()->create(['id' => 'eng', 'internal_name' => 'English', 'is_default' => true]);
        $context = Context::factory()->create(['internal_name' => 'Catalogue', 'is_default' => true]);
        $item = Item::factory()->Object()->create(['internal_name' => 'Temple relief']);
        $translation = ItemTranslation::factory()->create([
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Relief of the temple',
            'description' => 'A carved stone relief.',
        ]);

        $this->setCurrentPanel();

        // Create
        $langFr = Language::factory()->create(['id' => 'fra', 'internal_name' => 'French', 'is_default' => false]);
        Livewire::actingAs($user)
            ->test(CreateItemTranslation::class)
            ->fillForm([
                'item_id' => $item->id,
                'language_id' => $langFr->id,
                'context_id' => $context->id,
                'name' => 'Relief du temple',
                'description' => 'Un relief en pierre sculpté.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('item_translations', [
            'item_id' => $item->id,
            'language_id' => $langFr->id,
            'context_id' => $context->id,
            'name' => 'Relief du temple',
        ]);

        // Edit
        Livewire::actingAs($user)
            ->test(EditItemTranslation::class, ['record' => $translation->getRouteKey()])
            ->assertFormSet([
                'name' => 'Relief of the temple',
            ])
            ->fillForm(['name' => 'Temple wall relief', 'description' => 'Updated description.'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('item_translations', [
            'id' => $translation->id,
            'name' => 'Temple wall relief',
            'description' => 'Updated description.',
        ]);

        // Delete
        Livewire::actingAs($user)
            ->test(ListItemTranslation::class)
            ->callTableAction(DeleteAction::class, $translation)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('item_translations', ['id' => $translation->id]);
    }

    public function test_item_translation_duplicate_prevention(): void
    {
        $user = $this->createCrudUser();
        $language = Language::factory()->create(['id' => 'eng', 'internal_name' => 'English', 'is_default' => true]);
        $context = Context::factory()->create(['internal_name' => 'Catalogue', 'is_default' => true]);
        $item = Item::factory()->Object()->create(['internal_name' => 'Temple relief']);
        ItemTranslation::factory()->create([
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Relief of the temple',
        ]);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(CreateItemTranslation::class)
            ->fillForm([
                'item_id' => $item->id,
                'language_id' => $language->id,
                'context_id' => $context->id,
                'name' => 'Duplicate translation',
            ])
            ->call('create')
            ->assertHasFormErrors(['language_id']);
    }

    public function test_missing_fallback_filter_works_for_item_translations(): void
    {
        $user = $this->createCrudUser();
        $defaultLang = Language::factory()->create(['id' => 'eng', 'internal_name' => 'English', 'is_default' => true]);
        $otherLang = Language::factory()->create(['id' => 'fra', 'internal_name' => 'French', 'is_default' => false]);
        $defaultCtx = Context::factory()->create(['internal_name' => 'Catalogue', 'is_default' => true]);

        // Item A has no default translation
        $itemA = Item::factory()->Object()->create(['internal_name' => 'Item A']);
        $translationA = ItemTranslation::factory()->create([
            'item_id' => $itemA->id,
            'language_id' => $otherLang->id,
            'context_id' => $defaultCtx->id,
            'name' => 'Item A French',
        ]);

        // Item B has a default translation AND a non-default one
        $itemB = Item::factory()->Object()->create(['internal_name' => 'Item B']);
        $translationBDefault = ItemTranslation::factory()->create([
            'item_id' => $itemB->id,
            'language_id' => $defaultLang->id,
            'context_id' => $defaultCtx->id,
            'name' => 'Item B English',
        ]);
        $translationBFrench = ItemTranslation::factory()->create([
            'item_id' => $itemB->id,
            'language_id' => $otherLang->id,
            'context_id' => $defaultCtx->id,
            'name' => 'Item B French',
        ]);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(ListItemTranslation::class)
            ->filterTable('missing_fallback')
            ->assertCanSeeTableRecords([$translationA])
            ->assertCanNotSeeTableRecords([$translationBDefault, $translationBFrench]);
    }

    // ─── CollectionTranslation ──────────────────────────────────────────────────

    public function test_authorized_users_can_render_all_collection_translation_resource_pages(): void
    {
        $user = $this->createCrudUser();
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

        $this->actingAs($user)->get('/admin/collection-translations')
            ->assertOk()
            ->assertSee('Collection Translations');

        $this->actingAs($user)->get('/admin/collection-translations/create')
            ->assertOk()
            ->assertSee('Create');

        $this->actingAs($user)->get("/admin/collection-translations/{$translation->getKey()}/edit")
            ->assertOk()
            ->assertSee('Temple Collection EN');
    }

    public function test_authorized_users_can_crud_collection_translations(): void
    {
        $user = $this->createCrudUser();
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

        $this->setCurrentPanel();

        // Create
        $langFr = Language::factory()->create(['id' => 'fra', 'internal_name' => 'French', 'is_default' => false]);
        Livewire::actingAs($user)
            ->test(CreateCollectionTranslation::class)
            ->fillForm([
                'collection_id' => $collection->id,
                'language_id' => $langFr->id,
                'context_id' => $context->id,
                'title' => 'Collection du temple',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('collection_translations', [
            'collection_id' => $collection->id,
            'language_id' => $langFr->id,
            'context_id' => $context->id,
            'title' => 'Collection du temple',
        ]);

        // Edit
        Livewire::actingAs($user)
            ->test(EditCollectionTranslation::class, ['record' => $translation->getRouteKey()])
            ->assertFormSet(['title' => 'Temple Collection EN'])
            ->fillForm(['title' => 'Temple Collection Updated'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('collection_translations', [
            'id' => $translation->id,
            'title' => 'Temple Collection Updated',
        ]);

        // Delete
        Livewire::actingAs($user)
            ->test(ListCollectionTranslation::class)
            ->callTableAction(DeleteAction::class, $translation)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('collection_translations', ['id' => $translation->id]);
    }

    public function test_collection_translation_duplicate_prevention(): void
    {
        $user = $this->createCrudUser();
        $language = Language::factory()->create(['id' => 'eng', 'internal_name' => 'English', 'is_default' => true]);
        $context = Context::factory()->create(['internal_name' => 'Catalogue', 'is_default' => true]);
        $collection = Collection::factory()->create([
            'internal_name' => 'Temple collection',
            'context_id' => $context->id,
            'language_id' => $language->id,
        ]);
        CollectionTranslation::factory()->create([
            'collection_id' => $collection->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'title' => 'Temple Collection EN',
        ]);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(CreateCollectionTranslation::class)
            ->fillForm([
                'collection_id' => $collection->id,
                'language_id' => $language->id,
                'context_id' => $context->id,
                'title' => 'Duplicate collection translation',
            ])
            ->call('create')
            ->assertHasFormErrors(['language_id']);
    }

    // ─── PartnerTranslation ─────────────────────────────────────────────────────

    public function test_authorized_users_can_render_all_partner_translation_resource_pages(): void
    {
        $user = $this->createCrudUser();
        $language = Language::factory()->create(['id' => 'eng', 'internal_name' => 'English', 'is_default' => true]);
        $context = Context::factory()->create(['internal_name' => 'Catalogue', 'is_default' => true]);
        $partner = Partner::factory()->create(['internal_name' => 'Jordan Museum']);
        $translation = PartnerTranslation::factory()->create([
            'partner_id' => $partner->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Jordan Museum EN',
        ]);

        $this->actingAs($user)->get('/admin/partner-translations')
            ->assertOk()
            ->assertSee('Partner Translations');

        $this->actingAs($user)->get('/admin/partner-translations/create')
            ->assertOk()
            ->assertSee('Create');

        $this->actingAs($user)->get("/admin/partner-translations/{$translation->getKey()}/edit")
            ->assertOk()
            ->assertSee('Jordan Museum EN');
    }

    public function test_authorized_users_can_crud_partner_translations(): void
    {
        $user = $this->createCrudUser();
        $language = Language::factory()->create(['id' => 'eng', 'internal_name' => 'English', 'is_default' => true]);
        $context = Context::factory()->create(['internal_name' => 'Catalogue', 'is_default' => true]);
        $partner = Partner::factory()->create(['internal_name' => 'Jordan Museum']);
        $translation = PartnerTranslation::factory()->create([
            'partner_id' => $partner->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Jordan Museum EN',
            'description' => 'Museum description.',
        ]);

        $this->setCurrentPanel();

        // Create
        $langFr = Language::factory()->create(['id' => 'fra', 'internal_name' => 'French', 'is_default' => false]);
        Livewire::actingAs($user)
            ->test(CreatePartnerTranslation::class)
            ->fillForm([
                'partner_id' => $partner->id,
                'language_id' => $langFr->id,
                'context_id' => $context->id,
                'name' => 'Musée de Jordanie',
                'description' => 'Description du musée.',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('partner_translations', [
            'partner_id' => $partner->id,
            'language_id' => $langFr->id,
            'context_id' => $context->id,
            'name' => 'Musée de Jordanie',
        ]);

        // Edit
        Livewire::actingAs($user)
            ->test(EditPartnerTranslation::class, ['record' => $translation->getRouteKey()])
            ->assertFormSet(['name' => 'Jordan Museum EN'])
            ->fillForm(['name' => 'Jordan Heritage Museum', 'description' => 'Updated description.'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('partner_translations', [
            'id' => $translation->id,
            'name' => 'Jordan Heritage Museum',
            'description' => 'Updated description.',
        ]);

        // Delete
        Livewire::actingAs($user)
            ->test(ListPartnerTranslation::class)
            ->callTableAction(DeleteAction::class, $translation)
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseMissing('partner_translations', ['id' => $translation->id]);
    }

    public function test_partner_translation_duplicate_prevention(): void
    {
        $user = $this->createCrudUser();
        $language = Language::factory()->create(['id' => 'eng', 'internal_name' => 'English', 'is_default' => true]);
        $context = Context::factory()->create(['internal_name' => 'Catalogue', 'is_default' => true]);
        $partner = Partner::factory()->create(['internal_name' => 'Jordan Museum']);
        PartnerTranslation::factory()->create([
            'partner_id' => $partner->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Jordan Museum EN',
        ]);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(CreatePartnerTranslation::class)
            ->fillForm([
                'partner_id' => $partner->id,
                'language_id' => $language->id,
                'context_id' => $context->id,
                'name' => 'Duplicate partner translation',
            ])
            ->call('create')
            ->assertHasFormErrors(['language_id']);
    }

    // ─── Helpers ────────────────────────────────────────────────────────────────

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

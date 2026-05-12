<?php

namespace Tests\Filament\Resources;

use App\Enums\Permission;
use App\Filament\Resources\CollectionResource\Pages\ViewCollection;
use App\Filament\Resources\CollectionResource\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\ItemResource\Pages\ViewItem;
use App\Filament\Resources\ItemResource\RelationManagers\CollectionAppearancesRelationManager;
use App\Models\Collection;
use App\Models\Context;
use App\Models\Item;
use App\Models\Language;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Tests covering THG-style contextual appearance text visibility in Filament.
 *
 * Uses self-contained factory data that matches the imported THG pivot extra shape:
 *   - extra.contextual_descriptions.{lang_id}: readable paragraph per language
 *   - extra.source_bc_by_language.{lang_id}: legacy provenance key per language
 */
class CollectionItemAppearanceTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Collection page — Items relation manager
    // -------------------------------------------------------------------------

    public function test_collection_items_relation_manager_shows_item_with_display_order(): void
    {
        $user = $this->createCrudUser();
        $collection = Collection::factory()->create(['internal_name' => 'THG Collection']);
        $item = Item::factory()->Object()->create(['internal_name' => 'THG Relief']);

        $collection->attachedItems()->attach($item->id, [
            'display_order' => 5,
            'extra' => [
                'contextual_descriptions' => [
                    'eng' => 'A remarkable carved relief depicting the daily life of ancient communities.',
                ],
                'source_bc_by_language' => [
                    'eng' => 'THG-001-ENG',
                ],
            ],
        ]);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(ItemsRelationManager::class, [
                'ownerRecord' => $collection,
                'pageClass' => ViewCollection::class,
            ])
            ->assertCanSeeTableRecords([$item])
            ->assertTableColumnExists('pivot.display_order');
    }

    public function test_collection_items_relation_manager_shows_contextual_text_preview_column(): void
    {
        $user = $this->createCrudUser();
        $collection = Collection::factory()->create(['internal_name' => 'THG Collection']);
        $item = Item::factory()->Object()->create(['internal_name' => 'THG Relief']);
        Language::factory()->create(['id' => 'eng', 'internal_name' => 'English', 'is_default' => true]);
        Context::factory()->create(['internal_name' => 'Default', 'is_default' => true]);

        $collection->attachedItems()->attach($item->id, [
            'display_order' => 1,
            'extra' => [
                'contextual_descriptions' => [
                    'eng' => 'A remarkable carved relief depicting the daily life of ancient communities.',
                    'fra' => 'Un remarquable relief sculpté représentant la vie quotidienne des communautés anciennes.',
                ],
            ],
        ]);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(ItemsRelationManager::class, [
                'ownerRecord' => $collection,
                'pageClass' => ViewCollection::class,
            ])
            ->assertTableColumnExists('pivot.contextual_text_preview')
            ->assertTableColumnExists('pivot.contextual_description_languages');
    }

    public function test_collection_items_relation_manager_view_appearance_text_action_visible_when_pivot_has_data(): void
    {
        $user = $this->createCrudUser();
        $collection = Collection::factory()->create(['internal_name' => 'THG Collection']);
        $item = Item::factory()->Object()->create(['internal_name' => 'THG Relief']);

        $collection->attachedItems()->attach($item->id, [
            'display_order' => 2,
            'extra' => [
                'contextual_descriptions' => [
                    'eng' => 'A remarkable carved relief.',
                    'fra' => 'Un remarquable relief sculpté.',
                ],
                'source_bc_by_language' => [
                    'eng' => 'THG-001-ENG',
                ],
            ],
        ]);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(ItemsRelationManager::class, [
                'ownerRecord' => $collection,
                'pageClass' => ViewCollection::class,
            ])
            ->assertTableActionExists('view_appearance_text', record: $item);
    }

    public function test_collection_items_relation_manager_view_appearance_text_action_not_visible_when_no_pivot_data(): void
    {
        $user = $this->createCrudUser();
        $collection = Collection::factory()->create(['internal_name' => 'THG Collection']);
        $item = Item::factory()->Object()->create(['internal_name' => 'THG Relief']);

        $collection->attachedItems()->attach($item->id);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(ItemsRelationManager::class, [
                'ownerRecord' => $collection,
                'pageClass' => ViewCollection::class,
            ])
            ->assertTableActionHidden('view_appearance_text', record: $item);
    }

    // -------------------------------------------------------------------------
    // Item page — Collection appearances relation manager
    // -------------------------------------------------------------------------

    public function test_collection_appearances_relation_manager_shows_collection_for_item(): void
    {
        $user = $this->createCrudUser();
        $context = Context::factory()->create(['internal_name' => 'Catalogue']);
        $language = Language::factory()->create(['id' => 'eng', 'internal_name' => 'English']);
        $collection = Collection::factory()->create([
            'internal_name' => 'THG Collection',
            'context_id' => $context->id,
            'language_id' => $language->id,
        ]);
        $item = Item::factory()->Object()->create(['internal_name' => 'THG Relief']);

        $collection->attachedItems()->attach($item->id, [
            'display_order' => 3,
            'extra' => [
                'contextual_descriptions' => [
                    'eng' => 'A remarkable carved relief depicting the daily life of ancient communities.',
                    'fra' => 'Un remarquable relief sculpté.',
                ],
                'source_bc_by_language' => [
                    'eng' => 'THG-001-ENG',
                ],
            ],
        ]);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(CollectionAppearancesRelationManager::class, [
                'ownerRecord' => $item,
                'pageClass' => ViewItem::class,
            ])
            ->assertCanSeeTableRecords([$collection])
            ->assertTableColumnExists('pivot.display_order')
            ->assertTableColumnExists('pivot.contextual_text_preview')
            ->assertTableColumnExists('pivot.contextual_description_languages');
    }

    public function test_collection_appearances_relation_manager_view_appearance_text_action_visible_when_pivot_has_data(): void
    {
        $user = $this->createCrudUser();
        $context = Context::factory()->create(['internal_name' => 'Catalogue']);
        $language = Language::factory()->create(['id' => 'eng', 'internal_name' => 'English']);
        $collection = Collection::factory()->create([
            'internal_name' => 'THG Collection',
            'context_id' => $context->id,
            'language_id' => $language->id,
        ]);
        $item = Item::factory()->Object()->create(['internal_name' => 'THG Relief']);

        $collection->attachedItems()->attach($item->id, [
            'display_order' => 1,
            'extra' => [
                'contextual_descriptions' => [
                    'eng' => 'A remarkable carved relief.',
                    'fra' => 'Un remarquable relief sculpté.',
                ],
                'source_bc_by_language' => [
                    'eng' => 'THG-001-ENG',
                ],
            ],
        ]);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(CollectionAppearancesRelationManager::class, [
                'ownerRecord' => $item,
                'pageClass' => ViewItem::class,
            ])
            ->assertTableActionExists('view_appearance_text', record: $collection);
    }

    public function test_collection_appearances_relation_manager_view_appearance_text_action_not_visible_when_no_pivot_data(): void
    {
        $user = $this->createCrudUser();
        $context = Context::factory()->create(['internal_name' => 'Catalogue']);
        $language = Language::factory()->create(['id' => 'eng', 'internal_name' => 'English']);
        $collection = Collection::factory()->create([
            'internal_name' => 'THG Collection',
            'context_id' => $context->id,
            'language_id' => $language->id,
        ]);
        $item = Item::factory()->Object()->create(['internal_name' => 'THG Relief']);

        $collection->attachedItems()->attach($item->id);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(CollectionAppearancesRelationManager::class, [
                'ownerRecord' => $item,
                'pageClass' => ViewItem::class,
            ])
            ->assertTableActionHidden('view_appearance_text', record: $collection);
    }

    public function test_collection_appearances_relation_manager_shows_empty_state_when_item_not_in_any_collection(): void
    {
        $user = $this->createCrudUser();
        $context = Context::factory()->create(['internal_name' => 'Catalogue']);
        $language = Language::factory()->create(['id' => 'eng', 'internal_name' => 'English']);
        $item = Item::factory()->Object()->create(['internal_name' => 'Standalone item']);
        $otherCollection = Collection::factory()->create([
            'internal_name' => 'Other collection',
            'context_id' => $context->id,
            'language_id' => $language->id,
        ]);

        $this->setCurrentPanel();

        Livewire::actingAs($user)
            ->test(CollectionAppearancesRelationManager::class, [
                'ownerRecord' => $item,
                'pageClass' => ViewItem::class,
            ])
            ->assertCanNotSeeTableRecords([$otherCollection]);
    }

    protected function setCurrentPanel(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
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
}

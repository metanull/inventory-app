<?php

namespace Tests\Unit\Models;

use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for the CollectionItem typed pivot model.
 */
class CollectionItemTest extends TestCase
{
    use RefreshDatabase;

    public function test_collection_item_casts_display_order_to_integer(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->Object()->create();
        $collection->attachedItems()->attach($item->id, ['display_order' => 3]);

        /** @var CollectionItem $pivot */
        $pivot = $collection->attachedItems()->withPivot('display_order')->first()->pivot;

        $this->assertInstanceOf(CollectionItem::class, $pivot);
        $this->assertSame(3, $pivot->display_order);
        $this->assertIsInt($pivot->display_order);
    }

    public function test_collection_item_casts_extra_to_array(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->Object()->create();
        $extra = [
            'contextual_descriptions' => ['eng' => 'A description in English.'],
            'source_bc_by_language' => ['eng' => 'THG-001'],
        ];
        $collection->attachedItems()->attach($item->id, ['extra' => $extra]);

        /** @var CollectionItem $pivot */
        $pivot = $collection->attachedItems()->first()->pivot;

        $this->assertInstanceOf(CollectionItem::class, $pivot);
        $this->assertIsArray($pivot->extra);
        $this->assertArrayHasKey('contextual_descriptions', $pivot->extra);
    }

    public function test_contextual_descriptions_returns_array_from_extra(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->Object()->create();
        $extra = [
            'contextual_descriptions' => [
                'eng' => 'English contextual text.',
                'fra' => 'Texte contextuel en français.',
            ],
        ];
        $collection->attachedItems()->attach($item->id, ['extra' => $extra]);

        /** @var CollectionItem $pivot */
        $pivot = $collection->attachedItems()->first()->pivot;

        $result = $pivot->contextualDescriptions();

        $this->assertIsArray($result);
        $this->assertSame('English contextual text.', $result['eng']);
        $this->assertSame('Texte contextuel en français.', $result['fra']);
    }

    public function test_contextual_descriptions_returns_empty_array_when_extra_is_null(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->Object()->create();
        $collection->attachedItems()->attach($item->id);

        /** @var CollectionItem $pivot */
        $pivot = $collection->attachedItems()->first()->pivot;

        $this->assertSame([], $pivot->contextualDescriptions());
    }

    public function test_contextual_descriptions_returns_empty_array_when_key_missing(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->Object()->create();
        $extra = ['source_bc_by_language' => ['eng' => 'THG-001']];
        $collection->attachedItems()->attach($item->id, ['extra' => $extra]);

        /** @var CollectionItem $pivot */
        $pivot = $collection->attachedItems()->first()->pivot;

        $this->assertSame([], $pivot->contextualDescriptions());
    }

    public function test_contextual_description_for_language_returns_string_when_present(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->Object()->create();
        $extra = ['contextual_descriptions' => ['eng' => 'English text.']];
        $collection->attachedItems()->attach($item->id, ['extra' => $extra]);

        /** @var CollectionItem $pivot */
        $pivot = $collection->attachedItems()->first()->pivot;

        $this->assertSame('English text.', $pivot->contextualDescriptionForLanguage('eng'));
    }

    public function test_contextual_description_for_language_returns_null_when_missing(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->Object()->create();
        $collection->attachedItems()->attach($item->id);

        /** @var CollectionItem $pivot */
        $pivot = $collection->attachedItems()->first()->pivot;

        $this->assertNull($pivot->contextualDescriptionForLanguage('eng'));
    }

    public function test_contextual_description_languages_returns_keys(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->Object()->create();
        $extra = [
            'contextual_descriptions' => [
                'eng' => 'English text.',
                'fra' => 'French text.',
            ],
        ];
        $collection->attachedItems()->attach($item->id, ['extra' => $extra]);

        /** @var CollectionItem $pivot */
        $pivot = $collection->attachedItems()->first()->pivot;

        $languages = $pivot->contextualDescriptionLanguages();

        $this->assertEqualsCanonicalizing(['eng', 'fra'], $languages);
    }

    public function test_source_backward_compatibility_by_language_returns_array(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->Object()->create();
        $extra = [
            'source_bc_by_language' => [
                'eng' => 'THG-source-001',
                'fra' => 'THG-source-002',
            ],
        ];
        $collection->attachedItems()->attach($item->id, ['extra' => $extra]);

        /** @var CollectionItem $pivot */
        $pivot = $collection->attachedItems()->first()->pivot;

        $result = $pivot->sourceBackwardCompatibilityByLanguage();

        $this->assertIsArray($result);
        $this->assertSame('THG-source-001', $result['eng']);
        $this->assertSame('THG-source-002', $result['fra']);
    }

    public function test_source_backward_compatibility_by_language_returns_empty_when_missing(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->Object()->create();
        $collection->attachedItems()->attach($item->id);

        /** @var CollectionItem $pivot */
        $pivot = $collection->attachedItems()->first()->pivot;

        $this->assertSame([], $pivot->sourceBackwardCompatibilityByLanguage());
    }

    public function test_source_backward_compatibility_for_language_returns_value_when_present(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->Object()->create();
        $extra = ['source_bc_by_language' => ['eng' => 'THG-legacy-key']];
        $collection->attachedItems()->attach($item->id, ['extra' => $extra]);

        /** @var CollectionItem $pivot */
        $pivot = $collection->attachedItems()->first()->pivot;

        $this->assertSame('THG-legacy-key', $pivot->sourceBackwardCompatibilityForLanguage('eng'));
    }

    public function test_source_backward_compatibility_for_language_returns_null_when_absent(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->Object()->create();
        $collection->attachedItems()->attach($item->id);

        /** @var CollectionItem $pivot */
        $pivot = $collection->attachedItems()->first()->pivot;

        $this->assertNull($pivot->sourceBackwardCompatibilityForLanguage('fra'));
    }

    public function test_collection_relationship_resolves_to_collection_model(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->Object()->create();
        $collection->attachedItems()->attach($item->id);

        /** @var CollectionItem $pivot */
        $pivot = $collection->attachedItems()->first()->pivot;

        $this->assertInstanceOf(Collection::class, $pivot->collection);
        $this->assertTrue($pivot->collection->is($collection));
    }

    public function test_item_relationship_resolves_to_item_model(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->Object()->create();
        $collection->attachedItems()->attach($item->id);

        /** @var CollectionItem $pivot */
        $pivot = $collection->attachedItems()->first()->pivot;

        $this->assertInstanceOf(Item::class, $pivot->item);
        $this->assertTrue($pivot->item->is($item));
    }

    public function test_attached_items_relationship_uses_collection_item_pivot(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->Object()->create();
        $collection->attachedItems()->attach($item->id);

        $attachedItem = $collection->attachedItems()->first();

        $this->assertInstanceOf(CollectionItem::class, $attachedItem->pivot);
    }

    public function test_attached_to_collections_relationship_uses_collection_item_pivot(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->Object()->create();
        $collection->attachedItems()->attach($item->id);

        $attachedCollection = $item->attachedToCollections()->first();

        $this->assertInstanceOf(CollectionItem::class, $attachedCollection->pivot);
    }
}

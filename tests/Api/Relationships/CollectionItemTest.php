<?php

namespace Tests\Api\Relationships;

use App\Models\Collection;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Api\Traits\AuthenticatesApiRequests;
use Tests\TestCase;

class CollectionItemAttachTest extends TestCase
{
    use AuthenticatesApiRequests, RefreshDatabase;

    // #region ATTACH TESTS

    public function test_collection_item_attach_can_attach_item_to_collection(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->create();

        $response = $this->postJson(route('collection.attachItem', $collection->id), [
            'item_id' => $item->id,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'internal_name',
                ],
            ]);

        $this->assertCount(1, $collection->fresh()->attachedItems);
        $this->assertTrue($collection->fresh()->attachedItems->contains($item));
    }

    public function test_collection_item_attach_does_not_create_duplicate_when_item_already_attached(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->create();

        $collection->attachedItems()->attach($item);

        $response = $this->postJson(route('collection.attachItem', $collection->id), [
            'item_id' => $item->id,
        ]);

        $response->assertOk();

        $this->assertCount(1, $collection->fresh()->attachedItems);
    }

    public function test_collection_item_attach_validation_requires_item_id(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->postJson(route('collection.attachItem', $collection->id), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['item_id']);
    }

    public function test_collection_item_attach_validation_requires_valid_uuid_for_item_id(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->postJson(route('collection.attachItem', $collection->id), [
            'item_id' => 'invalid-uuid',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['item_id']);
    }

    public function test_collection_item_attach_validation_requires_existing_item(): void
    {
        $collection = Collection::factory()->create();
        $nonExistentUuid = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';

        $response = $this->postJson(route('collection.attachItem', $collection->id), [
            'item_id' => $nonExistentUuid,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['item_id']);
    }

    public function test_collection_item_attach_can_attach_item_with_include_parameter(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->create();

        $response = $this->postJson(route('collection.attachItem', [$collection->id, 'include' => 'attachedItems']), [
            'item_id' => $item->id,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'attachedItems' => [
                        '*' => [
                            'id',
                            'internal_name',
                        ],
                    ],
                ],
            ]);
    }

    public function test_collection_item_attach_returns_not_found_for_nonexistent_collection(): void
    {
        $item = Item::factory()->create();

        $response = $this->postJson(route('collection.attachItem', 'nonexistent-uuid'), [
            'item_id' => $item->id,
        ]);

        $response->assertNotFound();
    }

    // #endregion

    // #region DETACH TESTS

    public function test_collection_item_detach_can_detach_item_from_collection(): void
    {
        $collection = Collection::factory()->create();
        $items = Item::factory()->count(3)->create();
        $collection->attachedItems()->attach($items);

        $itemToDetach = $items->first();

        $response = $this->deleteJson(route('collection.detachItem', $collection->id), [
            'item_id' => $itemToDetach->id,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'internal_name',
                ],
            ]);

        $this->assertCount(2, $collection->fresh()->attachedItems);
        $this->assertFalse($collection->fresh()->attachedItems->contains($itemToDetach));
    }

    public function test_collection_item_detach_detaching_non_attached_item_does_not_cause_error(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->create();

        $response = $this->deleteJson(route('collection.detachItem', $collection->id), [
            'item_id' => $item->id,
        ]);

        $response->assertOk();
        $this->assertCount(0, $collection->fresh()->attachedItems);
    }

    public function test_collection_item_detach_validation_requires_item_id(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->deleteJson(route('collection.detachItem', $collection->id), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['item_id']);
    }

    public function test_collection_item_detach_validation_requires_valid_uuid_for_item_id(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->deleteJson(route('collection.detachItem', $collection->id), [
            'item_id' => 'invalid-uuid',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['item_id']);
    }

    public function test_collection_item_detach_validation_requires_existing_item(): void
    {
        $collection = Collection::factory()->create();
        $nonExistentUuid = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';

        $response = $this->deleteJson(route('collection.detachItem', $collection->id), [
            'item_id' => $nonExistentUuid,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['item_id']);
    }

    public function test_collection_item_detach_can_detach_item_with_include_parameter(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->create();
        $collection->attachedItems()->attach($item);

        $response = $this->deleteJson(route('collection.detachItem', [$collection->id, 'include' => 'attachedItems']), [
            'item_id' => $item->id,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'attachedItems',
                ],
            ]);
    }

    public function test_collection_item_detach_returns_not_found_for_nonexistent_collection(): void
    {
        $item = Item::factory()->create();

        $response = $this->deleteJson(route('collection.detachItem', 'nonexistent-uuid'), [
            'item_id' => $item->id,
        ]);

        $response->assertNotFound();
    }

    // #endregion

    // #region BULK ATTACH TESTS

    public function test_collection_item_bulk_attach_can_attach_multiple_items_to_collection(): void
    {
        $collection = Collection::factory()->create();
        $items = Item::factory()->count(3)->create();

        $response = $this->postJson(route('collection.attachItems', $collection->id), [
            'item_ids' => $items->pluck('id')->toArray(),
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'internal_name',
                ],
            ]);

        $this->assertCount(3, $collection->fresh()->attachedItems);
        foreach ($items as $item) {
            $this->assertTrue($collection->fresh()->attachedItems->contains($item));
        }
    }

    public function test_collection_item_bulk_attach_does_not_create_duplicates_when_some_items_already_attached(): void
    {
        $collection = Collection::factory()->create();
        $existingItems = Item::factory()->count(2)->create();
        $newItems = Item::factory()->count(2)->create();

        $collection->attachedItems()->attach($existingItems);

        $allItemIds = $existingItems->merge($newItems)->pluck('id')->toArray();

        $response = $this->postJson(route('collection.attachItems', $collection->id), [
            'item_ids' => $allItemIds,
        ]);

        $response->assertOk();

        $this->assertCount(4, $collection->fresh()->attachedItems);
    }

    public function test_collection_item_bulk_attach_validation_requires_item_ids(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->postJson(route('collection.attachItems', $collection->id), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['item_ids']);
    }

    public function test_collection_item_bulk_attach_validation_requires_item_ids_to_be_array(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->postJson(route('collection.attachItems', $collection->id), [
            'item_ids' => 'not-an-array',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['item_ids']);
    }

    public function test_collection_item_bulk_attach_validation_requires_at_least_one_item_id(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->postJson(route('collection.attachItems', $collection->id), [
            'item_ids' => [],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['item_ids']);
    }

    public function test_collection_item_bulk_attach_validation_requires_valid_uuids(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->postJson(route('collection.attachItems', $collection->id), [
            'item_ids' => ['invalid-uuid', 'another-invalid'],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['item_ids.0', 'item_ids.1']);
    }

    public function test_collection_item_bulk_attach_validation_requires_existing_items(): void
    {
        $collection = Collection::factory()->create();
        $nonExistentUuid1 = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';
        $nonExistentUuid2 = 'a47ac10b-58cc-4372-a567-0e02b2c3d470';

        $response = $this->postJson(route('collection.attachItems', $collection->id), [
            'item_ids' => [$nonExistentUuid1, $nonExistentUuid2],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['item_ids.0', 'item_ids.1']);
    }

    public function test_collection_item_bulk_attach_can_attach_items_with_include_parameter(): void
    {
        $collection = Collection::factory()->create();
        $items = Item::factory()->count(2)->create();

        $response = $this->postJson(route('collection.attachItems', [$collection->id, 'include' => 'attachedItems']), [
            'item_ids' => $items->pluck('id')->toArray(),
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'attachedItems' => [
                        '*' => [
                            'id',
                            'internal_name',
                        ],
                    ],
                ],
            ]);
    }

    public function test_collection_item_bulk_attach_returns_not_found_for_nonexistent_collection(): void
    {
        $items = Item::factory()->count(2)->create();

        $response = $this->postJson(route('collection.attachItems', 'nonexistent-uuid'), [
            'item_ids' => $items->pluck('id')->toArray(),
        ]);

        $response->assertNotFound();
    }

    // #endregion

    // #region BULK DETACH TESTS

    public function test_collection_item_bulk_detach_can_detach_multiple_items_from_collection(): void
    {
        $collection = Collection::factory()->create();
        $items = Item::factory()->count(5)->create();
        $collection->attachedItems()->attach($items);

        $itemsToDetach = $items->take(3);

        $response = $this->deleteJson(route('collection.detachItems', $collection->id), [
            'item_ids' => $itemsToDetach->pluck('id')->toArray(),
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'internal_name',
                ],
            ]);

        $this->assertCount(2, $collection->fresh()->attachedItems);
        foreach ($itemsToDetach as $item) {
            $this->assertFalse($collection->fresh()->attachedItems->contains($item));
        }
    }

    public function test_collection_item_bulk_detach_detaching_non_attached_items_does_not_cause_error(): void
    {
        $collection = Collection::factory()->create();
        $attachedItems = Item::factory()->count(2)->create();
        $nonAttachedItems = Item::factory()->count(2)->create();

        $collection->attachedItems()->attach($attachedItems);

        $response = $this->deleteJson(route('collection.detachItems', $collection->id), [
            'item_ids' => $nonAttachedItems->pluck('id')->toArray(),
        ]);

        $response->assertOk();
        $this->assertCount(2, $collection->fresh()->attachedItems);
    }

    public function test_collection_item_bulk_detach_validation_requires_item_ids(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->deleteJson(route('collection.detachItems', $collection->id), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['item_ids']);
    }

    public function test_collection_item_bulk_detach_validation_requires_item_ids_to_be_array(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->deleteJson(route('collection.detachItems', $collection->id), [
            'item_ids' => 'not-an-array',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['item_ids']);
    }

    public function test_collection_item_bulk_detach_validation_requires_at_least_one_item_id(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->deleteJson(route('collection.detachItems', $collection->id), [
            'item_ids' => [],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['item_ids']);
    }

    public function test_collection_item_bulk_detach_validation_requires_valid_uuids(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->deleteJson(route('collection.detachItems', $collection->id), [
            'item_ids' => ['invalid-uuid', 'another-invalid'],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['item_ids.0', 'item_ids.1']);
    }

    public function test_collection_item_bulk_detach_validation_requires_existing_items(): void
    {
        $collection = Collection::factory()->create();
        $nonExistentUuid1 = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';
        $nonExistentUuid2 = 'a47ac10b-58cc-4372-a567-0e02b2c3d470';

        $response = $this->deleteJson(route('collection.detachItems', $collection->id), [
            'item_ids' => [$nonExistentUuid1, $nonExistentUuid2],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['item_ids.0', 'item_ids.1']);
    }

    public function test_collection_item_bulk_detach_can_detach_items_with_include_parameter(): void
    {
        $collection = Collection::factory()->create();
        $items = Item::factory()->count(3)->create();
        $collection->attachedItems()->attach($items);

        $itemsToDetach = $items->take(2);

        $response = $this->deleteJson(route('collection.detachItems', [$collection->id, 'include' => 'attachedItems']), [
            'item_ids' => $itemsToDetach->pluck('id')->toArray(),
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'attachedItems',
                ],
            ]);
    }

    public function test_collection_item_bulk_detach_returns_not_found_for_nonexistent_collection(): void
    {
        $items = Item::factory()->count(2)->create();

        $response = $this->deleteJson(route('collection.detachItems', 'nonexistent-uuid'), [
            'item_ids' => $items->pluck('id')->toArray(),
        ]);

        $response->assertNotFound();
    }

    // #endregion
}

<?php

namespace Tests\Feature\Api\Collection;

use App\Enums\Permission;
use App\Models\Collection;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DetachItemsTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWith(Permission::dataOperations());
        $this->actingAs($this->user);
    }

    public function test_can_detach_multiple_items_from_collection(): void
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

    public function test_detaching_non_attached_items_does_not_cause_error(): void
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

    public function test_validation_requires_item_ids(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->deleteJson(route('collection.detachItems', $collection->id), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['item_ids']);
    }

    public function test_validation_requires_item_ids_to_be_array(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->deleteJson(route('collection.detachItems', $collection->id), [
            'item_ids' => 'not-an-array',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['item_ids']);
    }

    public function test_validation_requires_at_least_one_item_id(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->deleteJson(route('collection.detachItems', $collection->id), [
            'item_ids' => [],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['item_ids']);
    }

    public function test_validation_requires_valid_uuids(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->deleteJson(route('collection.detachItems', $collection->id), [
            'item_ids' => ['invalid-uuid', 'another-invalid'],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['item_ids.0', 'item_ids.1']);
    }

    public function test_validation_requires_existing_items(): void
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

    public function test_can_detach_items_with_include_parameter(): void
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

    public function test_returns_not_found_for_nonexistent_collection(): void
    {
        $items = Item::factory()->count(2)->create();

        $response = $this->deleteJson(route('collection.detachItems', 'nonexistent-uuid'), [
            'item_ids' => $items->pluck('id')->toArray(),
        ]);

        $response->assertNotFound();
    }
}

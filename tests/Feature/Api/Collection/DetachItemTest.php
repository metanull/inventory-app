<?php

namespace Tests\Feature\Api\Collection;

use App\Enums\Permission;
use App\Models\Collection;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class DetachItemTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createUserWith(Permission::dataOperations());
        $this->actingAs($this->user);
    }

    public function test_can_detach_item_from_collection(): void
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

    public function test_detaching_non_attached_item_does_not_cause_error(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->create();

        $response = $this->deleteJson(route('collection.detachItem', $collection->id), [
            'item_id' => $item->id,
        ]);

        $response->assertOk();
        $this->assertCount(0, $collection->fresh()->attachedItems);
    }

    public function test_validation_requires_item_id(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->deleteJson(route('collection.detachItem', $collection->id), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['item_id']);
    }

    public function test_validation_requires_valid_uuid_for_item_id(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->deleteJson(route('collection.detachItem', $collection->id), [
            'item_id' => 'invalid-uuid',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['item_id']);
    }

    public function test_validation_requires_existing_item(): void
    {
        $collection = Collection::factory()->create();
        $nonExistentUuid = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';

        $response = $this->deleteJson(route('collection.detachItem', $collection->id), [
            'item_id' => $nonExistentUuid,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['item_id']);
    }

    public function test_can_detach_item_with_include_parameter(): void
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

    public function test_returns_not_found_for_nonexistent_collection(): void
    {
        $item = Item::factory()->create();

        $response = $this->deleteJson(route('collection.detachItem', 'nonexistent-uuid'), [
            'item_id' => $item->id,
        ]);

        $response->assertNotFound();
    }
}

<?php

namespace Tests\Feature\Api\Collection;

use App\Models\Collection;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class AttachItemTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function test_can_attach_item_to_collection(): void
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

    public function test_does_not_create_duplicate_when_item_already_attached(): void
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

    public function test_validation_requires_item_id(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->postJson(route('collection.attachItem', $collection->id), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['item_id']);
    }

    public function test_validation_requires_valid_uuid_for_item_id(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->postJson(route('collection.attachItem', $collection->id), [
            'item_id' => 'invalid-uuid',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['item_id']);
    }

    public function test_validation_requires_existing_item(): void
    {
        $collection = Collection::factory()->create();
        $nonExistentUuid = 'f47ac10b-58cc-4372-a567-0e02b2c3d479';

        $response = $this->postJson(route('collection.attachItem', $collection->id), [
            'item_id' => $nonExistentUuid,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['item_id']);
    }

    public function test_can_attach_item_with_include_parameter(): void
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

    public function test_returns_not_found_for_nonexistent_collection(): void
    {
        $item = Item::factory()->create();

        $response = $this->postJson(route('collection.attachItem', 'nonexistent-uuid'), [
            'item_id' => $item->id,
        ]);

        $response->assertNotFound();
    }
}

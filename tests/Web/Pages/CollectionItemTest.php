<?php

namespace Tests\Web\Pages;

use App\Models\Collection;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

/**
 * Tests for Collection-Item relationship operations
 */
class CollectionItemTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_can_attach_item_to_collection(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->create();

        $response = $this->post(route('collections.attachItem', $collection), [
            'item_id' => $item->id,
        ]);

        $response->assertRedirect(route('collections.show', $collection));
        $this->assertDatabaseHas('collection_item', [
            'collection_id' => $collection->id,
            'item_id' => $item->id,
        ]);
    }

    public function test_can_detach_item_from_collection(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->create();
        $collection->attachItem($item);

        $response = $this->delete(route('collections.detachItem', [$collection, $item]));

        $response->assertRedirect(route('collections.show', $collection));
        $this->assertDatabaseMissing('collection_item', [
            'collection_id' => $collection->id,
            'item_id' => $item->id,
        ]);
    }

    public function test_attach_item_requires_authentication(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->create();

        auth()->logout();

        $response = $this->post(route('collections.attachItem', $collection), [
            'item_id' => $item->id,
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_detach_item_requires_authentication(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->create();
        $collection->attachItem($item);

        auth()->logout();

        $response = $this->delete(route('collections.detachItem', [$collection, $item]));

        $response->assertRedirect(route('login'));
    }

    public function test_attach_item_requires_valid_item_id(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->post(route('collections.attachItem', $collection), [
            'item_id' => 'invalid-uuid',
        ]);

        $response->assertSessionHasErrors('item_id');
    }

    public function test_attach_item_requires_existing_item(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->post(route('collections.attachItem', $collection), [
            'item_id' => '00000000-0000-0000-0000-000000000000',
        ]);

        $response->assertSessionHasErrors('item_id');
    }

    public function test_attach_item_requires_item_id(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->post(route('collections.attachItem', $collection), []);

        $response->assertSessionHasErrors('item_id');
    }
}

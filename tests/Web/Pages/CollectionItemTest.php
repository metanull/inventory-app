<?php

namespace Tests\Web\Pages;

use App\Models\Collection;
use App\Models\Item;
use App\Models\ItemImage;
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

    public function test_collection_show_renders_item_thumbnails_with_route_based_urls(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->create();
        $collection->attachItem($item);

        $image = ItemImage::factory()->create(['item_id' => $item->id]);

        $response = $this->get(route('collections.show', $collection));

        $response->assertOk();
        $expectedUrl = route('items.item-images.view', [$item, $image]);
        $response->assertSee($expectedUrl, false);
    }

    public function test_show_item_in_collection_context(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->create();
        $collection->attachItem($item);

        $response = $this->get(route('collections.items.show', [$collection, $item]));

        $response->assertOk();
        $response->assertSee($item->internal_name);
        $response->assertSee($collection->internal_name);
        $response->assertSee(route('collections.index'), false);
    }

    public function test_show_item_in_collection_context_has_collection_breadcrumbs(): void
    {
        $parent = Collection::factory()->create(['internal_name' => 'Parent Col']);
        $child = Collection::factory()->create([
            'internal_name' => 'Child Col',
            'parent_id' => $parent->id,
            'language_id' => $parent->language_id,
            'context_id' => $parent->context_id,
        ]);
        $item = Item::factory()->create(['internal_name' => 'Test Item']);
        $child->attachItem($item);

        $response = $this->get(route('collections.items.show', [$child, $item]));

        $response->assertOk();
        $response->assertSee('Parent Col');
        $response->assertSee('Child Col');
        $response->assertSee(route('collections.show', $parent), false);
        $response->assertSee(route('collections.show', $child), false);
    }

    public function test_show_item_with_item_ancestors_in_collection_context(): void
    {
        $collection = Collection::factory()->create(['internal_name' => 'The Collection']);
        $parentItem = Item::factory()->create(['internal_name' => 'Parent Item']);
        $childItem = Item::factory()->create([
            'internal_name' => 'Child Item',
            'parent_id' => $parentItem->id,
        ]);
        $collection->attachItem($childItem);

        $response = $this->get(route('collections.items.show', [$collection, $childItem]));

        $response->assertOk();
        $response->assertSee('The Collection');
        $response->assertSee('Parent Item');
        $response->assertSee(route('collections.items.show', [$collection, $parentItem]), false);
    }

    public function test_show_item_in_collection_context_requires_authentication(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->create();

        auth()->logout();

        $response = $this->get(route('collections.items.show', [$collection, $item]));

        $response->assertRedirect(route('login'));
    }

    public function test_collection_show_links_items_to_collection_scoped_route(): void
    {
        $collection = Collection::factory()->create();
        $item = Item::factory()->create();
        $collection->attachItem($item);

        $response = $this->get(route('collections.show', $collection));

        $response->assertOk();
        $response->assertSee(route('collections.items.show', [$collection, $item]), false);
    }
}

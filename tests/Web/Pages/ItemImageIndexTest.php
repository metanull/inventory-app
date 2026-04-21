<?php

namespace Tests\Web\Pages;

use App\Models\Item;
use App\Models\ItemImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class ItemImageIndexTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_index_renders_request_driven_page_without_livewire_markup(): void
    {
        $item = Item::factory()->create(['internal_name' => 'Test Item']);
        ItemImage::factory()->forItem($item)->create(['original_name' => 'artifact.jpg']);

        $response = $this->get(route('items.item-images.index', $item));

        $response
            ->assertOk()
            ->assertViewIs('item-images.index')
            ->assertSee('artifact.jpg');

        $this->assertStringNotContainsString('livewire:dynamic-component', $response->getContent());
        $this->assertStringNotContainsString('wire:click', $response->getContent());
    }

    public function test_index_scopes_images_to_parent_item(): void
    {
        $item = Item::factory()->create(['internal_name' => 'My Item']);
        $otherItem = Item::factory()->create(['internal_name' => 'Other Item']);

        ItemImage::factory()->forItem($item)->create(['original_name' => 'matching-image.jpg']);
        ItemImage::factory()->forItem($otherItem)->create(['original_name' => 'other-image.jpg']);

        $response = $this->get(route('items.item-images.index', $item));

        $response
            ->assertOk()
            ->assertSee('matching-image.jpg')
            ->assertDontSee('other-image.jpg');
    }

    public function test_index_returns_not_found_for_non_existent_item(): void
    {
        $response = $this->get(route('items.item-images.index', ['item' => 'non-existent-uuid']));

        $response->assertNotFound();
    }

    public function test_index_can_search_by_original_name(): void
    {
        $item = Item::factory()->create();

        ItemImage::factory()->forItem($item)->create(['original_name' => 'temple-vase.jpg']);
        ItemImage::factory()->forItem($item)->create(['original_name' => 'museum-pot.jpg']);

        $response = $this->get(route('items.item-images.index', ['item' => $item, 'q' => 'temple-vase']));

        $response
            ->assertOk()
            ->assertSee('temple-vase.jpg')
            ->assertDontSee('museum-pot.jpg');
    }

    public function test_index_can_sort_by_display_order(): void
    {
        $item = Item::factory()->create();

        ItemImage::factory()->forItem($item)->withOrder(2)->create(['original_name' => 'second.jpg']);
        ItemImage::factory()->forItem($item)->withOrder(1)->create(['original_name' => 'first.jpg']);

        $response = $this->get(route('items.item-images.index', [
            'item' => $item,
            'sort' => 'display_order',
            'direction' => 'asc',
        ]));

        $response
            ->assertOk()
            ->assertSeeInOrder(['first.jpg', 'second.jpg']);
    }

    public function test_index_rejects_invalid_sort_field_gracefully(): void
    {
        $item = Item::factory()->create();

        $response = $this->get(route('items.item-images.index', [
            'item' => $item,
            'sort' => 'invalid_field',
        ]));

        $response->assertOk();
    }

    public function test_index_requires_view_data_permission(): void
    {
        $item = Item::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('items.item-images.index', $item));

        $response->assertForbidden();
    }

    public function test_index_passes_parent_item_to_view(): void
    {
        $item = Item::factory()->create(['internal_name' => 'My Special Item']);

        $response = $this->get(route('items.item-images.index', $item));

        $response
            ->assertOk()
            ->assertSee('My Special Item');

        $this->assertSame($item->id, $response->viewData('item')->id);
    }

    public function test_index_contains_upload_zone_component(): void
    {
        $item = Item::factory()->create();

        $response = $this->get(route('items.item-images.index', $item));

        $response
            ->assertOk()
            ->assertSee('imageUploadZone');
    }
}

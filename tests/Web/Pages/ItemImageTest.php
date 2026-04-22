<?php

namespace Tests\Web\Pages;

use App\Models\AvailableImage;
use App\Models\Item;
use App\Models\ItemImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class ItemImageTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_create_requires_authentication(): void
    {
        auth()->logout();
        $item = Item::factory()->create();

        $response = $this->get(route('items.item-images.create', ['item' => $item]));

        $response->assertRedirect(route('login'));
    }

    public function test_create_page_passes_available_images_from_controller(): void
    {
        $item = Item::factory()->create();
        AvailableImage::factory()->count(3)->create();

        $response = $this->get(route('items.item-images.create', ['item' => $item]));

        $response->assertOk()
            ->assertViewIs('item-images.create')
            ->assertViewHas('availableImages')
            ->assertViewHas('item');
    }

    public function test_edit_requires_authentication(): void
    {
        auth()->logout();
        $item = Item::factory()->create();
        $itemImage = ItemImage::factory()->forItem($item)->create();

        $response = $this->get(route('items.item-images.edit', ['item' => $item, 'item_image' => $itemImage]));

        $response->assertRedirect(route('login'));
    }

    public function test_edit_page_passes_item_and_image_from_controller(): void
    {
        $item = Item::factory()->create();
        $itemImage = ItemImage::factory()->forItem($item)->create();

        $response = $this->get(route('items.item-images.edit', ['item' => $item, 'item_image' => $itemImage]));

        $response->assertOk()
            ->assertViewIs('item-images.edit')
            ->assertViewHas('item', $item)
            ->assertViewHas('itemImage', $itemImage);
    }

    public function test_edit_returns_404_for_image_belonging_to_different_item(): void
    {
        $item = Item::factory()->create();
        $otherItem = Item::factory()->create();
        $itemImage = ItemImage::factory()->forItem($otherItem)->create();

        $response = $this->get(route('items.item-images.edit', ['item' => $item, 'item_image' => $itemImage]));

        $response->assertNotFound();
    }
}

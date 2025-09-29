<?php

namespace Tests\Feature\Api\ItemImage;

use App\Models\Item;
use App\Models\ItemImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_index_forbids_anonymous_access(): void
    {
        $item = Item::factory()->create();
        $response = $this->getJson(route('item.images.index', $item->id));
        $response->assertUnauthorized();
    }

    public function test_show_forbids_anonymous_access(): void
    {
        $itemImage = ItemImage::factory()->create();
        $response = $this->getJson(route('item-image.show', $itemImage->id));
        $response->assertUnauthorized();
    }

    public function test_store_forbids_anonymous_access(): void
    {
        $item = Item::factory()->create();
        $response = $this->postJson(route('item.images.store', $item->id), [
            'path' => 'https://example.com/image.jpg',
            'original_name' => 'test-image.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 150000,
            'alt_text' => 'Test image',
        ]);
        $response->assertUnauthorized();
    }

    public function test_update_forbids_anonymous_access(): void
    {
        $itemImage = ItemImage::factory()->create();
        $response = $this->patchJson(route('item-image.update', $itemImage->id), [
            'alt_text' => 'Updated alt text',
        ]);
        $response->assertUnauthorized();
    }

    public function test_destroy_forbids_anonymous_access(): void
    {
        $itemImage = ItemImage::factory()->create();
        $response = $this->deleteJson(route('item-image.destroy', $itemImage->id));
        $response->assertUnauthorized();
    }

    public function test_move_up_forbids_anonymous_access(): void
    {
        $itemImage = ItemImage::factory()->create();
        $response = $this->patchJson(route('item-image.moveUp', $itemImage->id));
        $response->assertUnauthorized();
    }

    public function test_move_down_forbids_anonymous_access(): void
    {
        $itemImage = ItemImage::factory()->create();
        $response = $this->patchJson(route('item-image.moveDown', $itemImage->id));
        $response->assertUnauthorized();
    }

    public function test_attach_from_available_forbids_anonymous_access(): void
    {
        $item = Item::factory()->create();
        $response = $this->postJson(route('item.attachImage', $item->id), [
            'available_image_id' => 'test-uuid',
            'alt_text' => 'Test alt text',
        ]);
        $response->assertUnauthorized();
    }

    public function test_detach_to_available_forbids_anonymous_access(): void
    {
        $itemImage = ItemImage::factory()->create();
        $response = $this->postJson(route('item-image.detach', $itemImage->id));
        $response->assertUnauthorized();
    }
}

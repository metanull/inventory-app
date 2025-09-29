<?php

namespace Tests\Feature\Api\ItemImage;

use App\Models\Item;
use App\Models\ItemImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_index_allows_authenticated_users(): void
    {
        $item = Item::factory()->create();
        $response = $this->getJson(route('item.images.index', $item->id));
        $response->assertOk();
    }

    public function test_index_returns_ok_when_no_data(): void
    {
        $item = Item::factory()->create();
        $response = $this->getJson(route('item.images.index', $item->id));
        $response->assertOk();
    }

    public function test_index_returns_an_empty_array_when_no_data(): void
    {
        $item = Item::factory()->create();
        $response = $this->getJson(route('item.images.index', $item->id));
        $response->assertJsonCount(0, 'data');
    }

    public function test_index_returns_the_expected_structure(): void
    {
        $item = Item::factory()->create();
        $itemImage = ItemImage::factory()->forItem($item)->create();
        $response = $this->getJson(route('item.images.index', $item->id));

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'item_id',
                    'path',
                    'original_name',
                    'mime_type',
                    'size',
                    'alt_text',
                    'display_order',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    public function test_index_returns_item_images_in_display_order(): void
    {
        $item = Item::factory()->create();
        $image3 = ItemImage::factory()->forItem($item)->withOrder(3)->create();
        $image1 = ItemImage::factory()->forItem($item)->withOrder(1)->create();
        $image2 = ItemImage::factory()->forItem($item)->withOrder(2)->create();

        $response = $this->getJson(route('item.images.index', $item->id));

        $response->assertOk();
        $response->assertJsonCount(3, 'data');

        // Check that images are returned in display order
        $data = $response->json('data');
        $this->assertEquals($image1->id, $data[0]['id']);
        $this->assertEquals($image2->id, $data[1]['id']);
        $this->assertEquals($image3->id, $data[2]['id']);
    }

    public function test_index_only_returns_images_for_specified_item(): void
    {
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();

        $image1 = ItemImage::factory()->forItem($item1)->create();
        $image2 = ItemImage::factory()->forItem($item2)->create();

        $response = $this->getJson(route('item.images.index', $item1->id));

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $this->assertEquals($image1->id, $response->json('data.0.id'));
    }

    public function test_index_returns_not_found_for_nonexistent_item(): void
    {
        $response = $this->getJson(route('item.images.index', 'nonexistent-uuid'));
        $response->assertNotFound();
    }

    public function test_index_returns_empty_for_item_with_no_images(): void
    {
        $item = Item::factory()->create();
        $response = $this->getJson(route('item.images.index', $item->id));

        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_index_includes_relationships_when_requested(): void
    {
        $item = Item::factory()->create();
        $itemImage = ItemImage::factory()->forItem($item)->create();

        $response = $this->getJson(route('item.images.index', $item->id).'?include=item');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'item_id',
                    'path',
                    'original_name',
                    'mime_type',
                    'size',
                    'alt_text',
                    'display_order',
                    'created_at',
                    'updated_at',
                    'item' => [
                        'id',
                        'internal_name',
                        'type',
                    ],
                ],
            ],
        ]);
    }
}

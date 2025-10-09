<?php

namespace Tests\Feature\Api\ItemImage;

use App\Models\Item;
use App\Models\ItemImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class StoreTest extends TestCase
{
    use CreatesUsersWithPermissions;
    use RefreshDatabase;
    use WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function test_store_creates_item_image_successfully(): void
    {
        $item = Item::factory()->create();
        $data = [
            'path' => 'https://example.com/test-image.jpg',
            'original_name' => 'test-image.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 150000,
            'alt_text' => 'Test image description',
            'display_order' => 1,
        ];

        $response = $this->postJson(route('item.images.store', $item->id), $data);

        $response->assertCreated();
        $this->assertDatabaseHas('item_images', [
            'item_id' => $item->id,
            'path' => $data['path'],
            'original_name' => $data['original_name'],
            'mime_type' => $data['mime_type'],
            'size' => $data['size'],
            'alt_text' => $data['alt_text'],
            'display_order' => $data['display_order'],
        ]);
    }

    public function test_store_returns_correct_structure(): void
    {
        $item = Item::factory()->create();
        $data = ItemImage::factory()->make()->toArray();
        unset($data['item_id']); // Remove item_id as it's provided in the route

        $response = $this->postJson(route('item.images.store', $item->id), $data);

        $response->assertCreated();
        $response->assertJsonStructure([
            'data' => [
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
        ]);
    }

    public function test_store_auto_assigns_display_order_when_not_provided(): void
    {
        $item = Item::factory()->create();

        // Create existing images
        ItemImage::factory()->forItem($item)->withOrder(1)->create();
        ItemImage::factory()->forItem($item)->withOrder(2)->create();

        $data = [
            'path' => 'https://example.com/test-image.jpg',
            'original_name' => 'test-image.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 150000,
            'alt_text' => 'Test image description',
        ];

        $response = $this->postJson(route('item.images.store', $item->id), $data);

        $response->assertCreated();
        $response->assertJsonPath('data.display_order', 3);
    }

    public function test_store_validates_required_fields(): void
    {
        $item = Item::factory()->create();

        $response = $this->postJson(route('item.images.store', $item->id), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['path', 'original_name', 'mime_type', 'size']);
    }

    public function test_store_validates_path_format(): void
    {
        $item = Item::factory()->create();
        $data = ItemImage::factory()->make()->toArray();
        $data['path'] = 'invalid-url';

        $response = $this->postJson(route('item.images.store', $item->id), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['path']);
    }

    public function test_store_validates_mime_type(): void
    {
        $item = Item::factory()->create();
        $data = ItemImage::factory()->make()->toArray();
        $data['mime_type'] = 'invalid-mime-type';

        $response = $this->postJson(route('item.images.store', $item->id), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['mime_type']);
    }

    public function test_store_validates_size_is_positive_integer(): void
    {
        $item = Item::factory()->create();
        $data = ItemImage::factory()->make()->toArray();
        $data['size'] = -100;

        $response = $this->postJson(route('item.images.store', $item->id), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['size']);
    }

    public function test_store_validates_display_order_is_positive_integer(): void
    {
        $item = Item::factory()->create();
        $data = ItemImage::factory()->make()->toArray();
        $data['display_order'] = 0;

        $response = $this->postJson(route('item.images.store', $item->id), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['display_order']);
    }

    public function test_store_allows_null_alt_text(): void
    {
        $item = Item::factory()->create();
        $data = ItemImage::factory()->make(['alt_text' => null])->toArray();

        $response = $this->postJson(route('item.images.store', $item->id), $data);

        $response->assertCreated();
        $response->assertJsonPath('data.alt_text', null);
    }

    public function test_store_validates_alt_text_max_length(): void
    {
        $item = Item::factory()->create();
        $data = ItemImage::factory()->make()->toArray();
        $data['alt_text'] = str_repeat('a', 501); // Assuming max length is 500

        $response = $this->postJson(route('item.images.store', $item->id), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['alt_text']);
    }

    public function test_store_returns_not_found_for_nonexistent_item(): void
    {
        $data = ItemImage::factory()->make()->toArray();

        $response = $this->postJson(route('item.images.store', 'nonexistent-uuid'), $data);

        $response->assertNotFound();
    }

    public function test_store_creates_multiple_images_for_same_item(): void
    {
        $item = Item::factory()->create();

        for ($i = 1; $i <= 3; $i++) {
            $data = [
                'path' => "https://example.com/test-image-{$i}.jpg",
                'original_name' => "test-image-{$i}.jpg",
                'mime_type' => 'image/jpeg',
                'size' => 150000 + $i,
                'alt_text' => "Test image {$i} description",
                'display_order' => $i,
            ];

            $response = $this->postJson(route('item.images.store', $item->id), $data);
            $response->assertCreated();
        }

        $this->assertEquals(3, $item->itemImages()->count());
    }
}

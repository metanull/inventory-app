<?php

namespace Tests\Feature\Api\ItemImage;

use App\Models\Item;
use App\Models\ItemImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_update_modifies_item_image_successfully(): void
    {
        $itemImage = ItemImage::factory()->create([
            'alt_text' => 'Original alt text',
            'display_order' => 1,
        ]);

        $data = [
            'alt_text' => 'Updated alt text',
            'display_order' => 2,
        ];

        $response = $this->patchJson(route('item-image.update', $itemImage->id), $data);

        $response->assertOk();
        $this->assertDatabaseHas('item_images', [
            'id' => $itemImage->id,
            'alt_text' => 'Updated alt text',
            'display_order' => 2,
        ]);
    }

    public function test_update_returns_correct_structure(): void
    {
        $itemImage = ItemImage::factory()->create();
        $data = ['alt_text' => 'Updated alt text'];

        $response = $this->patchJson(route('item-image.update', $itemImage->id), $data);

        $response->assertOk();
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

    public function test_update_allows_partial_updates(): void
    {
        $itemImage = ItemImage::factory()->create([
            'alt_text' => 'Original alt text',
            'display_order' => 1,
        ]);

        $response = $this->patchJson(route('item-image.update', $itemImage->id), [
            'alt_text' => 'Only alt text updated',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.alt_text', 'Only alt text updated');
        $response->assertJsonPath('data.display_order', 1); // Should remain unchanged
    }

    public function test_update_validates_display_order_is_positive(): void
    {
        $itemImage = ItemImage::factory()->create();

        $response = $this->patchJson(route('item-image.update', $itemImage->id), [
            'display_order' => 0,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['display_order']);
    }

    public function test_update_validates_alt_text_max_length(): void
    {
        $itemImage = ItemImage::factory()->create();

        $response = $this->patchJson(route('item-image.update', $itemImage->id), [
            'alt_text' => str_repeat('a', 501), // Assuming max length is 500
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['alt_text']);
    }

    public function test_update_allows_null_alt_text(): void
    {
        $itemImage = ItemImage::factory()->create(['alt_text' => 'Some text']);

        $response = $this->patchJson(route('item-image.update', $itemImage->id), [
            'alt_text' => null,
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.alt_text', null);
    }

    public function test_update_prevents_changing_immutable_fields(): void
    {
        $itemImage = ItemImage::factory()->create();
        $originalPath = $itemImage->path;
        $originalItemId = $itemImage->item_id;

        $response = $this->patchJson(route('item-image.update', $itemImage->id), [
            'path' => 'https://example.com/new-path.jpg',
            'item_id' => Item::factory()->create()->id,
            'original_name' => 'new-name.jpg',
            'mime_type' => 'image/png',
            'size' => 999999,
        ]);

        // Should succeed but ignore immutable fields
        $response->assertOk();

        $itemImage->refresh();
        $this->assertEquals($originalPath, $itemImage->path);
        $this->assertEquals($originalItemId, $itemImage->item_id);
    }

    public function test_update_returns_not_found_for_nonexistent_item_image(): void
    {
        $response = $this->patchJson(route('item-image.update', 'nonexistent-uuid'), [
            'alt_text' => 'Updated text',
        ]);

        $response->assertNotFound();
    }

    public function test_update_handles_display_order_conflicts(): void
    {
        $item = Item::factory()->create();
        $image1 = ItemImage::factory()->forItem($item)->withOrder(1)->create();
        $image2 = ItemImage::factory()->forItem($item)->withOrder(2)->create();
        $image3 = ItemImage::factory()->forItem($item)->withOrder(3)->create();

        // Update image3 to have the same order as image1
        $response = $this->patchJson(route('item-image.update', $image3->id), [
            'display_order' => 1,
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.display_order', 1);

        $this->assertDatabaseHas('item_images', [
            'id' => $image3->id,
            'display_order' => 1,
        ]);
    }

    public function test_update_with_include_parameter(): void
    {
        $itemImage = ItemImage::factory()->create();

        $response = $this->patchJson(
            route('item-image.update', $itemImage->id).'?include=item',
            ['alt_text' => 'Updated with item']
        );

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'item_id',
                'alt_text',
                'item' => [
                    'id',
                    'internal_name',
                    'type',
                ],
            ],
        ]);
    }

    public function test_update_preserves_unchanged_fields(): void
    {
        $itemImage = ItemImage::factory()->create([
            'alt_text' => 'Original alt text',
            'display_order' => 5,
        ]);

        $originalAltText = $itemImage->alt_text;
        $originalDisplayOrder = $itemImage->display_order;

        // Update only alt_text
        $response = $this->patchJson(route('item-image.update', $itemImage->id), [
            'alt_text' => 'New alt text',
        ]);

        $response->assertOk();

        $itemImage->refresh();

        // Verify the update occurred
        $this->assertNotEquals($originalAltText, $itemImage->alt_text);
        $this->assertEquals('New alt text', $itemImage->alt_text);
        $this->assertEquals($originalDisplayOrder, $itemImage->display_order); // Unchanged

        // Verify database state
        $this->assertDatabaseHas('item_images', [
            'id' => $itemImage->id,
            'alt_text' => 'New alt text',
            'display_order' => 5,
        ]);
    }
}

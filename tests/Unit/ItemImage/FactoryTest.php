<?php

namespace Tests\Unit\ItemImage;

use App\Models\Item;
use App\Models\ItemImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_factory(): void
    {
        $itemImage = ItemImage::factory()->create();
        $this->assertInstanceOf(ItemImage::class, $itemImage);
        $this->assertNotEmpty($itemImage->id);
        $this->assertNotEmpty($itemImage->item_id);
        $this->assertNotEmpty($itemImage->path);
        $this->assertNotEmpty($itemImage->original_name);
        $this->assertNotEmpty($itemImage->mime_type);
        $this->assertNotNull($itemImage->size);
        $this->assertNotNull($itemImage->display_order);
    }

    public function test_factory_make(): void
    {
        $itemImage = ItemImage::factory()->make();
        $this->assertInstanceOf(ItemImage::class, $itemImage);
        $this->assertNull($itemImage->id); // Not saved to database
        $this->assertNotEmpty($itemImage->path);
        $this->assertNotEmpty($itemImage->original_name);
        $this->assertNotEmpty($itemImage->mime_type);
        $this->assertNotNull($itemImage->size);
        $this->assertNotNull($itemImage->display_order);
    }

    public function test_factory_for_item(): void
    {
        $item = Item::factory()->create();
        $itemImage = ItemImage::factory()->forItem($item)->make();

        $this->assertInstanceOf(ItemImage::class, $itemImage);
        $this->assertEquals($item->id, $itemImage->item_id);
    }

    public function test_factory_with_order(): void
    {
        $order = 5;
        $itemImage = ItemImage::factory()->withOrder($order)->make();

        $this->assertInstanceOf(ItemImage::class, $itemImage);
        $this->assertEquals($order, $itemImage->display_order);
    }

    public function test_factory_museum_object(): void
    {
        $itemImage = ItemImage::factory()->museumObject()->make();

        $this->assertInstanceOf(ItemImage::class, $itemImage);
        $this->assertStringContainsString('museum_object_', $itemImage->original_name);
        $this->assertEquals('image/jpeg', $itemImage->mime_type);
        $this->assertStringContainsString('Museum object:', $itemImage->alt_text);
        $this->assertGreaterThanOrEqual(200000, $itemImage->size); // At least 200KB
    }

    public function test_factory_monument(): void
    {
        $itemImage = ItemImage::factory()->monument()->make();

        $this->assertInstanceOf(ItemImage::class, $itemImage);
        $this->assertStringContainsString('monument_', $itemImage->original_name);
        $this->assertEquals('image/jpeg', $itemImage->mime_type);
        $this->assertStringContainsString('Historical monument:', $itemImage->alt_text);
        $this->assertGreaterThanOrEqual(300000, $itemImage->size); // At least 300KB
    }

    public function test_factory_generates_valid_mime_types(): void
    {
        $validMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];

        for ($i = 0; $i < 10; $i++) {
            $itemImage = ItemImage::factory()->make();
            $this->assertContains($itemImage->mime_type, $validMimeTypes);
        }
    }

    public function test_factory_generates_reasonable_file_sizes(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $itemImage = ItemImage::factory()->make();
            $this->assertGreaterThanOrEqual(50000, $itemImage->size); // At least 50KB
            $this->assertLessThanOrEqual(2000000, $itemImage->size); // At most 2MB
        }
    }

    public function test_factory_generates_valid_display_orders(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $itemImage = ItemImage::factory()->make();
            $this->assertGreaterThanOrEqual(1, $itemImage->display_order);
            $this->assertLessThanOrEqual(10, $itemImage->display_order);
        }
    }

    public function test_factory_alt_text_is_reasonable_length(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $itemImage = ItemImage::factory()->make();
            if ($itemImage->alt_text) {
                $this->assertGreaterThan(5, strlen($itemImage->alt_text));
                $this->assertLessThan(500, strlen($itemImage->alt_text));
            }
        }
    }

    public function test_factory_path_is_valid_storage_path(): void
    {
        $itemImage = ItemImage::factory()->make();
        // Path should be a relative storage path, not a URL
        $this->assertIsString($itemImage->path);
        $this->assertStringStartsWith('images/', $itemImage->path);
        $this->assertStringContainsString('.', $itemImage->path); // Has extension
    }

    public function test_factory_original_name_has_extension(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $itemImage = ItemImage::factory()->make();
            $this->assertStringContainsString('.', $itemImage->original_name);
            $this->assertTrue(pathinfo($itemImage->original_name, PATHINFO_EXTENSION) !== '');
        }
    }
}

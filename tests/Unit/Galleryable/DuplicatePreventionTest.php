<?php

namespace Tests\Unit\Galleryable;

use App\Models\Detail;
use App\Models\Gallery;
use App\Models\Item;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Galleryable Duplicate Prevention Test
 *
 * Tests that the galleryables pivot table properly prevents duplicate
 * gallery-item and gallery-detail relationships.
 */
class DuplicatePreventionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /**
     * Test that duplicate gallery-item attachment is prevented.
     */
    public function test_prevents_duplicate_gallery_item_attachment(): void
    {
        $gallery = Gallery::factory()->create();
        $item = Item::factory()->create();

        // First attachment should succeed
        $gallery->items()->attach($item->id, [
            'order' => 1,
            'backward_compatibility' => null,
        ]);

        $this->assertDatabaseHas('galleryables', [
            'gallery_id' => $gallery->id,
            'galleryable_id' => $item->id,
            'galleryable_type' => Item::class,
        ]);

        // Second attachment of the same item to the same gallery should fail
        $this->expectException(QueryException::class);

        $gallery->items()->attach($item->id, [
            'order' => 2,
            'backward_compatibility' => 'different',
        ]);
    }

    /**
     * Test that duplicate gallery-detail attachment is prevented.
     */
    public function test_prevents_duplicate_gallery_detail_attachment(): void
    {
        $gallery = Gallery::factory()->create();
        $detail = Detail::factory()->create();

        // First attachment should succeed
        $gallery->details()->attach($detail->id, [
            'order' => 1,
            'backward_compatibility' => null,
        ]);

        $this->assertDatabaseHas('galleryables', [
            'gallery_id' => $gallery->id,
            'galleryable_id' => $detail->id,
            'galleryable_type' => Detail::class,
        ]);

        // Second attachment of the same detail to the same gallery should fail
        $this->expectException(QueryException::class);

        $gallery->details()->attach($detail->id, [
            'order' => 2,
            'backward_compatibility' => 'different',
        ]);
    }

    /**
     * Test that same item can be attached to different galleries.
     */
    public function test_allows_same_item_in_different_galleries(): void
    {
        $gallery1 = Gallery::factory()->create();
        $gallery2 = Gallery::factory()->create();
        $item = Item::factory()->create();

        // Attach item to first gallery
        $gallery1->items()->attach($item->id, [
            'order' => 1,
            'backward_compatibility' => null,
        ]);

        // Attach same item to second gallery should succeed
        $gallery2->items()->attach($item->id, [
            'order' => 1,
            'backward_compatibility' => null,
        ]);

        $this->assertDatabaseHas('galleryables', [
            'gallery_id' => $gallery1->id,
            'galleryable_id' => $item->id,
            'galleryable_type' => Item::class,
        ]);

        $this->assertDatabaseHas('galleryables', [
            'gallery_id' => $gallery2->id,
            'galleryable_id' => $item->id,
            'galleryable_type' => Item::class,
        ]);

        $this->assertEquals(1, $gallery1->items()->count());
        $this->assertEquals(1, $gallery2->items()->count());
    }

    /**
     * Test that same detail can be attached to different galleries.
     */
    public function test_allows_same_detail_in_different_galleries(): void
    {
        $gallery1 = Gallery::factory()->create();
        $gallery2 = Gallery::factory()->create();
        $detail = Detail::factory()->create();

        // Attach detail to first gallery
        $gallery1->details()->attach($detail->id, [
            'order' => 1,
            'backward_compatibility' => null,
        ]);

        // Attach same detail to second gallery should succeed
        $gallery2->details()->attach($detail->id, [
            'order' => 1,
            'backward_compatibility' => null,
        ]);

        $this->assertDatabaseHas('galleryables', [
            'gallery_id' => $gallery1->id,
            'galleryable_id' => $detail->id,
            'galleryable_type' => Detail::class,
        ]);

        $this->assertDatabaseHas('galleryables', [
            'gallery_id' => $gallery2->id,
            'galleryable_id' => $detail->id,
            'galleryable_type' => Detail::class,
        ]);

        $this->assertEquals(1, $gallery1->details()->count());
        $this->assertEquals(1, $gallery2->details()->count());
    }

    /**
     * Test that multiple different items can be attached to the same gallery.
     */
    public function test_allows_multiple_items_in_same_gallery(): void
    {
        $gallery = Gallery::factory()->create();
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();

        // Attach first item
        $gallery->items()->attach($item1->id, [
            'order' => 1,
            'backward_compatibility' => null,
        ]);

        // Attach second item should succeed
        $gallery->items()->attach($item2->id, [
            'order' => 2,
            'backward_compatibility' => null,
        ]);

        $this->assertEquals(2, $gallery->items()->count());
        $this->assertDatabaseHas('galleryables', [
            'gallery_id' => $gallery->id,
            'galleryable_id' => $item1->id,
            'galleryable_type' => Item::class,
        ]);
        $this->assertDatabaseHas('galleryables', [
            'gallery_id' => $gallery->id,
            'galleryable_id' => $item2->id,
            'galleryable_type' => Item::class,
        ]);
    }
}

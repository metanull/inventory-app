<?php

namespace Tests\Unit\Tag;

use App\Models\Item;
use App\Models\Tag;
use App\Models\TagItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Factory: test that the factory creates a valid Tag.
     */
    public function test_factory()
    {
        $tag = Tag::factory()->create();
        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertNotNull($tag->internal_name);
        $this->assertNotNull($tag->backward_compatibility);
        $this->assertNotNull($tag->description);
    }

    public function test_factory_creates_a_row_in_database(): void
    {
        $tag = Tag::factory()->create();

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'internal_name' => $tag->internal_name,
            'backward_compatibility' => $tag->backward_compatibility,
            'description' => $tag->description,
        ]);
    }

    public function test_factory_complies_with_constraints(): void
    {
        $tag = Tag::factory()->create();

        // Test internal_name is required
        $this->assertNotNull($tag->internal_name);
        $this->assertIsString($tag->internal_name);

        // Test backward_compatibility is nullable
        $this->assertTrue(is_null($tag->backward_compatibility) || is_string($tag->backward_compatibility));

        // Test description is required
        $this->assertNotNull($tag->description);
        $this->assertIsString($tag->description);

        // Test UUID id
        $this->assertIsString($tag->id);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $tag->id);
    }

    public function test_factory_creates_unique_internal_names(): void
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $this->assertNotEquals($tag1->internal_name, $tag2->internal_name);
    }

    public function test_factory_generates_words_for_description(): void
    {
        $tag = Tag::factory()->create();

        // The description should contain multiple words (faker->words(5))
        $this->assertGreaterThan(10, strlen($tag->description)); // Should be more than just a single word
        $this->assertStringContainsString(' ', $tag->description); // Should contain spaces between words
    }

    public function test_scope_for_item_returns_tags_for_specific_item(): void
    {
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $tag3 = Tag::factory()->create();

        // Associate tags with items
        TagItem::factory()->create(['tag_id' => $tag1->id, 'item_id' => $item1->id]);
        TagItem::factory()->create(['tag_id' => $tag2->id, 'item_id' => $item1->id]);
        TagItem::factory()->create(['tag_id' => $tag3->id, 'item_id' => $item2->id]);

        $tagsForItem1 = Tag::forItem($item1)->get();
        $tagsForItem2 = Tag::forItem($item2)->get();

        $this->assertCount(2, $tagsForItem1);
        $this->assertCount(1, $tagsForItem2);
        $this->assertTrue($tagsForItem1->contains('id', $tag1->id));
        $this->assertTrue($tagsForItem1->contains('id', $tag2->id));
        $this->assertFalse($tagsForItem1->contains('id', $tag3->id));
        $this->assertTrue($tagsForItem2->contains('id', $tag3->id));
    }

    public function test_scope_for_item_works_with_item_id_string(): void
    {
        $item = Item::factory()->create();
        $tag = Tag::factory()->create();

        TagItem::factory()->create(['tag_id' => $tag->id, 'item_id' => $item->id]);

        $tagsForItem = Tag::forItem($item->id)->get();

        $this->assertCount(1, $tagsForItem);
        $this->assertTrue($tagsForItem->contains('id', $tag->id));
    }

    public function test_scope_for_item_returns_empty_when_no_tags(): void
    {
        $item = Item::factory()->create();

        $tagsForItem = Tag::forItem($item)->get();

        $this->assertCount(0, $tagsForItem);
    }
}

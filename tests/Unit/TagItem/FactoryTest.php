<?php

namespace Tests\Unit\TagItem;

use App\Models\Item;
use App\Models\Tag;
use App\Models\TagItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_factory_creates_valid_tag_item(): void
    {
        $tagItem = TagItem::factory()->create();

        $this->assertInstanceOf(TagItem::class, $tagItem);
        $this->assertDatabaseHas('tag_items', [
            'id' => $tagItem->id,
            'tag_id' => $tagItem->tag_id,
            'item_id' => $tagItem->item_id,
        ]);
        $this->assertNotNull($tagItem->id);
        $this->assertNotNull($tagItem->tag_id);
        $this->assertNotNull($tagItem->item_id);
        $this->assertNotNull($tagItem->created_at);
        $this->assertNotNull($tagItem->updated_at);
    }

    public function test_factory_creates_tag_item_with_existing_tag_and_item(): void
    {
        $tag = Tag::factory()->create();
        $item = Item::factory()->create();

        $tagItem = TagItem::factory()->create([
            'tag_id' => $tag->id,
            'item_id' => $item->id,
        ]);

        $this->assertDatabaseHas('tag_items', [
            'id' => $tagItem->id,
            'tag_id' => $tag->id,
            'item_id' => $item->id,
        ]);
        $this->assertEquals($tag->id, $tagItem->tag_id);
        $this->assertEquals($item->id, $tagItem->item_id);
    }

    public function test_factory_creates_multiple_tag_items(): void
    {
        $tagItems = TagItem::factory()->count(3)->create();

        $this->assertCount(3, $tagItems);
        foreach ($tagItems as $tagItem) {
            $this->assertInstanceOf(TagItem::class, $tagItem);
            $this->assertDatabaseHas('tag_items', [
                'id' => $tagItem->id,
            ]);
        }
    }

    public function test_factory_creates_tag_item_with_relationships(): void
    {
        $tagItem = TagItem::factory()->create();
        $tagItem->load(['tag', 'item']);

        $this->assertInstanceOf(Tag::class, $tagItem->tag);
        $this->assertInstanceOf(Item::class, $tagItem->item);
        $this->assertEquals($tagItem->tag_id, $tagItem->tag->id);
        $this->assertEquals($tagItem->item_id, $tagItem->item->id);
    }

    public function test_factory_generates_unique_tag_item_combinations(): void
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();

        $tagItem1 = TagItem::factory()->create([
            'tag_id' => $tag1->id,
            'item_id' => $item1->id,
        ]);

        $tagItem2 = TagItem::factory()->create([
            'tag_id' => $tag2->id,
            'item_id' => $item2->id,
        ]);

        $this->assertNotEquals($tagItem1->id, $tagItem2->id);
        $this->assertDatabaseHas('tag_items', [
            'tag_id' => $tag1->id,
            'item_id' => $item1->id,
        ]);
        $this->assertDatabaseHas('tag_items', [
            'tag_id' => $tag2->id,
            'item_id' => $item2->id,
        ]);
    }
}

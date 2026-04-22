<?php

namespace Tests\Unit\Models;

use App\Models\Item;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for Tag model scopes.
 */
class TagScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_for_item_returns_tags_for_specific_item(): void
    {
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();

        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $tag3 = Tag::factory()->create();

        // Associate tags with items
        $item1->tags()->attach([$tag1->id, $tag2->id]);
        $item2->tags()->attach($tag3->id);

        $tagsForItem1 = Tag::forItem($item1)->get();
        $tagsForItem2 = Tag::forItem($item2)->get();

        $this->assertCount(2, $tagsForItem1);
        $this->assertTrue($tagsForItem1->contains('id', $tag1->id));
        $this->assertTrue($tagsForItem1->contains('id', $tag2->id));
        $this->assertFalse($tagsForItem1->contains('id', $tag3->id));

        $this->assertCount(1, $tagsForItem2);
        $this->assertTrue($tagsForItem2->contains('id', $tag3->id));
    }

    public function test_scope_for_item_works_with_item_id_string(): void
    {
        $item = Item::factory()->create();
        $tag = Tag::factory()->create();

        $item->tags()->attach($tag->id);

        $tagsForItem = Tag::forItem($item->id)->get();

        $this->assertCount(1, $tagsForItem);
        $this->assertTrue($tagsForItem->contains('id', $tag->id));
    }

    public function test_scope_not_attached_to_excludes_tags_already_on_item(): void
    {
        $item = Item::factory()->create();
        $attachedTag = Tag::factory()->create();
        $unattachedTag = Tag::factory()->create();

        $item->tags()->attach($attachedTag->id);

        $results = Tag::notAttachedTo($item->id)->get();

        $this->assertFalse($results->contains('id', $attachedTag->id));
        $this->assertTrue($results->contains('id', $unattachedTag->id));
    }

    public function test_scope_not_attached_to_returns_all_when_no_tags_attached(): void
    {
        $item = Item::factory()->create();
        Tag::factory()->count(3)->create();

        $results = Tag::notAttachedTo($item->id)->get();

        $this->assertCount(3, $results);
    }
}

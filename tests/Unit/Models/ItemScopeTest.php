<?php

namespace Tests\Unit\Models;

use App\Models\Item;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for Item model scopes.
 *
 * These tests verify the business logic of model query scopes.
 */
class ItemScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_for_tag_returns_items_for_specific_tag(): void
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();
        $item3 = Item::factory()->create();

        // Associate items with tags using Eloquent relationships
        $tag1->items()->attach([$item1->id, $item2->id]);
        $tag2->items()->attach($item3->id);

        $itemsForTag1 = Item::forTag($tag1)->get();
        $itemsForTag2 = Item::forTag($tag2)->get();

        $this->assertCount(2, $itemsForTag1);
        $this->assertCount(1, $itemsForTag2);
        $this->assertTrue($itemsForTag1->contains('id', $item1->id));
        $this->assertTrue($itemsForTag1->contains('id', $item2->id));
        $this->assertFalse($itemsForTag1->contains('id', $item3->id));
        $this->assertTrue($itemsForTag2->contains('id', $item3->id));
    }

    public function test_scope_with_all_tags_returns_items_with_all_specified_tags(): void
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $tag3 = Tag::factory()->create();
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();
        $item3 = Item::factory()->create();

        // Item1 has tag1 and tag2
        $item1->tags()->attach([$tag1->id, $tag2->id]);

        // Item2 has only tag1
        $item2->tags()->attach($tag1->id);

        // Item3 has tag2 and tag3
        $item3->tags()->attach([$tag2->id, $tag3->id]);

        $itemsWithBothTags = Item::withAllTags([$tag1->id, $tag2->id])->get();

        $this->assertCount(1, $itemsWithBothTags);
        $this->assertTrue($itemsWithBothTags->contains('id', $item1->id));
    }

    public function test_scope_with_any_tags_returns_items_with_any_specified_tags(): void
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $tag3 = Tag::factory()->create();
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();
        $item3 = Item::factory()->create();

        // Item1 has tag1
        $item1->tags()->attach($tag1->id);

        // Item2 has tag2
        $item2->tags()->attach($tag2->id);

        // Item3 has tag3 (not in our search)
        $item3->tags()->attach($tag3->id);

        $itemsWithAnyTags = Item::withAnyTags([$tag1->id, $tag2->id])->get();

        $this->assertCount(2, $itemsWithAnyTags);
        $this->assertTrue($itemsWithAnyTags->contains('id', $item1->id));
        $this->assertTrue($itemsWithAnyTags->contains('id', $item2->id));
        $this->assertFalse($itemsWithAnyTags->contains('id', $item3->id));
    }

    public function test_scope_with_all_tags_works_with_tag_models(): void
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $item = Item::factory()->create();

        $item->tags()->attach([$tag1->id, $tag2->id]);

        $itemsWithBothTags = Item::withAllTags([$tag1, $tag2])->get();

        $this->assertCount(1, $itemsWithBothTags);
        $this->assertTrue($itemsWithBothTags->contains('id', $item->id));
    }

    public function test_scope_with_any_tags_works_with_tag_models(): void
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $item = Item::factory()->create();

        $item->tags()->attach($tag1->id);

        $itemsWithAnyTags = Item::withAnyTags([$tag1, $tag2])->get();

        $this->assertCount(1, $itemsWithAnyTags);
        $this->assertTrue($itemsWithAnyTags->contains('id', $item->id));
    }

    public function test_scope_excluding_ids_excludes_given_ids(): void
    {
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();
        $item3 = Item::factory()->create();

        $results = Item::excludingIds([$item1->id, $item2->id])->get();

        $this->assertFalse($results->contains('id', $item1->id));
        $this->assertFalse($results->contains('id', $item2->id));
        $this->assertTrue($results->contains('id', $item3->id));
    }

    public function test_scope_excluding_ids_with_empty_array_returns_all(): void
    {
        Item::factory()->count(3)->create();

        $results = Item::excludingIds([])->get();

        $this->assertCount(3, $results);
    }

    public function test_scope_excluding_descendants_of_excludes_self_and_children(): void
    {
        $root = Item::factory()->create();
        $child = Item::factory()->withParent($root)->create();
        $grandchild = Item::factory()->withParent($child)->create();
        $unrelated = Item::factory()->create();

        $results = Item::excludingDescendantsOf($root->id)->get();

        $this->assertFalse($results->contains('id', $root->id));
        $this->assertFalse($results->contains('id', $child->id));
        $this->assertFalse($results->contains('id', $grandchild->id));
        $this->assertTrue($results->contains('id', $unrelated->id));
    }

    public function test_scope_excluding_descendants_of_only_excludes_own_subtree(): void
    {
        $branch1 = Item::factory()->create();
        $branch1Child = Item::factory()->withParent($branch1)->create();
        $branch2 = Item::factory()->create();
        $branch2Child = Item::factory()->withParent($branch2)->create();

        $results = Item::excludingDescendantsOf($branch1->id)->get();

        $this->assertFalse($results->contains('id', $branch1->id));
        $this->assertFalse($results->contains('id', $branch1Child->id));
        $this->assertTrue($results->contains('id', $branch2->id));
        $this->assertTrue($results->contains('id', $branch2Child->id));
    }

    public function test_scope_excluding_ancestors_of_excludes_self_and_parents(): void
    {
        $grandparent = Item::factory()->create();
        $parent = Item::factory()->withParent($grandparent)->create();
        $subject = Item::factory()->withParent($parent)->create();
        $unrelated = Item::factory()->create();

        $results = Item::excludingAncestorsOf($subject->id)->get();

        $this->assertFalse($results->contains('id', $subject->id));
        $this->assertFalse($results->contains('id', $parent->id));
        $this->assertFalse($results->contains('id', $grandparent->id));
        $this->assertTrue($results->contains('id', $unrelated->id));
    }

    public function test_scope_excluding_ancestors_of_leaves_siblings_visible(): void
    {
        $parent = Item::factory()->create();
        $subject = Item::factory()->withParent($parent)->create();
        $sibling = Item::factory()->withParent($parent)->create();

        $results = Item::excludingAncestorsOf($subject->id)->get();

        $this->assertFalse($results->contains('id', $subject->id));
        $this->assertFalse($results->contains('id', $parent->id));
        $this->assertTrue($results->contains('id', $sibling->id));
    }
}

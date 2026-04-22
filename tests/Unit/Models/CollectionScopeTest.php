<?php

namespace Tests\Unit\Models;

use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for Collection model scopes.
 */
class CollectionScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_collections_returns_only_collection_type(): void
    {
        $collection1 = Collection::factory()->collection()->create();
        $collection2 = Collection::factory()->collection()->create();
        $exhibition = Collection::factory()->exhibition()->create();
        $gallery = Collection::factory()->gallery()->create();

        $results = Collection::collections()->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $collection1->id));
        $this->assertTrue($results->contains('id', $collection2->id));
        $this->assertFalse($results->contains('id', $exhibition->id));
        $this->assertFalse($results->contains('id', $gallery->id));
    }

    public function test_scope_exhibitions_returns_only_exhibition_type(): void
    {
        $collection = Collection::factory()->collection()->create();
        $exhibition1 = Collection::factory()->exhibition()->create();
        $exhibition2 = Collection::factory()->exhibition()->create();
        $gallery = Collection::factory()->gallery()->create();

        $results = Collection::exhibitions()->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $exhibition1->id));
        $this->assertTrue($results->contains('id', $exhibition2->id));
        $this->assertFalse($results->contains('id', $collection->id));
        $this->assertFalse($results->contains('id', $gallery->id));
    }

    public function test_scope_galleries_returns_only_gallery_type(): void
    {
        $collection = Collection::factory()->collection()->create();
        $exhibition = Collection::factory()->exhibition()->create();
        $gallery1 = Collection::factory()->gallery()->create();
        $gallery2 = Collection::factory()->gallery()->create();

        $results = Collection::galleries()->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $gallery1->id));
        $this->assertTrue($results->contains('id', $gallery2->id));
        $this->assertFalse($results->contains('id', $collection->id));
        $this->assertFalse($results->contains('id', $exhibition->id));
    }

    public function test_scope_excluding_ids_excludes_given_ids(): void
    {
        $col1 = Collection::factory()->collection()->create();
        $col2 = Collection::factory()->collection()->create();
        $col3 = Collection::factory()->collection()->create();

        $results = Collection::excludingIds([$col1->id, $col2->id])->get();

        $this->assertFalse($results->contains('id', $col1->id));
        $this->assertFalse($results->contains('id', $col2->id));
        $this->assertTrue($results->contains('id', $col3->id));
    }

    public function test_scope_excluding_ids_with_empty_array_returns_all(): void
    {
        Collection::factory()->collection()->count(3)->create();

        $results = Collection::excludingIds([])->get();

        $this->assertCount(3, $results);
    }

    public function test_scope_excluding_descendants_of_excludes_self_and_children(): void
    {
        $root = Collection::factory()->collection()->create();
        $child = Collection::factory()->collection()->withParent($root->id)->create();
        $grandchild = Collection::factory()->collection()->withParent($child->id)->create();
        $unrelated = Collection::factory()->collection()->create();

        $results = Collection::excludingDescendantsOf($root->id)->get();

        $this->assertFalse($results->contains('id', $root->id));
        $this->assertFalse($results->contains('id', $child->id));
        $this->assertFalse($results->contains('id', $grandchild->id));
        $this->assertTrue($results->contains('id', $unrelated->id));
    }

    public function test_scope_excluding_ancestors_of_excludes_self_and_parents(): void
    {
        $grandparent = Collection::factory()->collection()->create();
        $parent = Collection::factory()->collection()->withParent($grandparent->id)->create();
        $subject = Collection::factory()->collection()->withParent($parent->id)->create();
        $unrelated = Collection::factory()->collection()->create();

        $results = Collection::excludingAncestorsOf($subject->id)->get();

        $this->assertFalse($results->contains('id', $subject->id));
        $this->assertFalse($results->contains('id', $parent->id));
        $this->assertFalse($results->contains('id', $grandparent->id));
        $this->assertTrue($results->contains('id', $unrelated->id));
    }
}

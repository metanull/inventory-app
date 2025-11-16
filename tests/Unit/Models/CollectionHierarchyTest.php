<?php

namespace Tests\Unit\Models;

use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionHierarchyTest extends TestCase
{
    use RefreshDatabase;

    public function test_collection_can_have_parent(): void
    {
        $parent = Collection::factory()->create();
        $child = Collection::factory()->withParent($parent->id)->create();

        $this->assertNotNull($child->parent_id);
        $this->assertEquals($parent->id, $child->parent_id);
        $this->assertTrue($child->parent->is($parent));
    }

    public function test_collection_can_have_children(): void
    {
        $parent = Collection::factory()->create();
        $child1 = Collection::factory()->withParent($parent->id)->create();
        $child2 = Collection::factory()->withParent($parent->id)->create();

        $this->assertCount(2, $parent->children);
        $this->assertTrue($parent->children->contains($child1));
        $this->assertTrue($parent->children->contains($child2));
    }

    public function test_collection_without_parent_is_root(): void
    {
        $root = Collection::factory()->create(['parent_id' => null]);

        $this->assertNull($root->parent_id);
    }

    public function test_scope_roots_returns_only_collections_without_parent(): void
    {
        $root1 = Collection::factory()->create(['parent_id' => null]);
        $root2 = Collection::factory()->create(['parent_id' => null]);
        $parent = Collection::factory()->create(['parent_id' => null]);
        $child = Collection::factory()->withParent($parent->id)->create();

        $roots = Collection::roots()->get();

        $this->assertCount(3, $roots);
        $this->assertTrue($roots->contains($root1));
        $this->assertTrue($roots->contains($root2));
        $this->assertTrue($roots->contains($parent));
        $this->assertFalse($roots->contains($child));
    }

    public function test_scope_children_of_returns_children_of_specific_parent(): void
    {
        $parent = Collection::factory()->create();
        $child1 = Collection::factory()->withParent($parent->id)->create();
        $child2 = Collection::factory()->withParent($parent->id)->create();

        $otherParent = Collection::factory()->create();
        $otherChild = Collection::factory()->withParent($otherParent->id)->create();

        $children = Collection::childrenOf($parent->id)->get();

        $this->assertCount(2, $children);
        $this->assertTrue($children->contains($child1));
        $this->assertTrue($children->contains($child2));
        $this->assertFalse($children->contains($otherChild));
    }

    public function test_deleting_parent_cascades_to_children(): void
    {
        $parent = Collection::factory()->create();
        $child = Collection::factory()->withParent($parent->id)->create();

        $parentId = $parent->id;
        $childId = $child->id;

        $parent->delete();

        $this->assertNull(Collection::find($parentId));
        $this->assertNull(Collection::find($childId));
    }
}

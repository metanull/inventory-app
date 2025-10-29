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
}

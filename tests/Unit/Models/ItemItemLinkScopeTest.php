<?php

namespace Tests\Unit\Models;

use App\Models\Context;
use App\Models\Item;
use App\Models\ItemItemLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for ItemItemLink model scopes.
 *
 * These tests verify the business logic of model query scopes.
 */
class ItemItemLinkScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_from_source_filters_by_source_item(): void
    {
        $source = Item::factory()->create();
        $otherSource = Item::factory()->create();
        $target = Item::factory()->create();

        $link1 = ItemItemLink::factory()
            ->between($source, $target)
            ->create();
        $link2 = ItemItemLink::factory()
            ->between($otherSource, $target)
            ->create();

        $results = ItemItemLink::fromSource($source)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains('id', $link1->id));
        $this->assertFalse($results->contains('id', $link2->id));
    }

    public function test_scope_from_source_accepts_item_model(): void
    {
        $source = Item::factory()->create();
        $target = Item::factory()->create();

        $link = ItemItemLink::factory()
            ->between($source, $target)
            ->create();

        // Test passing Item model directly
        $results = ItemItemLink::fromSource($source)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains('id', $link->id));
    }

    public function test_scope_to_target_filters_by_target_item(): void
    {
        $source = Item::factory()->create();
        $target = Item::factory()->create();
        $otherTarget = Item::factory()->create();

        $link1 = ItemItemLink::factory()
            ->between($source, $target)
            ->create();
        $link2 = ItemItemLink::factory()
            ->between($source, $otherTarget)
            ->create();

        $results = ItemItemLink::toTarget($target)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains('id', $link1->id));
        $this->assertFalse($results->contains('id', $link2->id));
    }

    public function test_scope_to_target_accepts_item_model(): void
    {
        $source = Item::factory()->create();
        $target = Item::factory()->create();

        $link = ItemItemLink::factory()
            ->between($source, $target)
            ->create();

        // Test passing Item model directly
        $results = ItemItemLink::toTarget($target)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains('id', $link->id));
    }

    public function test_scope_in_context_filters_by_context(): void
    {
        $context = Context::factory()->create();
        $otherContext = Context::factory()->create();

        $link1 = ItemItemLink::factory()->inContext($context)->create();
        $link2 = ItemItemLink::factory()->inContext($otherContext)->create();

        $results = ItemItemLink::inContext($context)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains('id', $link1->id));
        $this->assertFalse($results->contains('id', $link2->id));
    }

    public function test_scope_in_context_accepts_context_model(): void
    {
        $context = Context::factory()->create();

        $link = ItemItemLink::factory()->inContext($context)->create();

        // Test passing Context model directly
        $results = ItemItemLink::inContext($context)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains('id', $link->id));
    }

    public function test_scope_involving_item_finds_links_as_source_or_target(): void
    {
        $item = Item::factory()->create();
        $other1 = Item::factory()->create();
        $other2 = Item::factory()->create();
        $other3 = Item::factory()->create();

        $linkAsSource = ItemItemLink::factory()
            ->between($item, $other1)
            ->create();
        $linkAsTarget = ItemItemLink::factory()
            ->between($other2, $item)
            ->create();
        $linkNeither = ItemItemLink::factory()
            ->between($other2, $other3)
            ->create();

        $results = ItemItemLink::involvingItem($item)->get();

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('id', $linkAsSource->id));
        $this->assertTrue($results->contains('id', $linkAsTarget->id));
        $this->assertFalse($results->contains('id', $linkNeither->id));
    }

    public function test_scope_involving_item_accepts_item_model(): void
    {
        $item = Item::factory()->create();
        $other = Item::factory()->create();

        $linkAsSource = ItemItemLink::factory()
            ->between($item, $other)
            ->create();

        // Test passing Item model directly
        $results = ItemItemLink::involvingItem($item)->get();

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains('id', $linkAsSource->id));
    }
}

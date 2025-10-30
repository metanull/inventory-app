<?php

namespace Tests\Unit\Models;

use App\Models\ItemItemLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for ItemItemLink factory.
 */
class ItemItemLinkFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_valid_item_item_link(): void
    {
        $link = ItemItemLink::factory()->create();

        $this->assertInstanceOf(ItemItemLink::class, $link);
        $this->assertNotEmpty($link->id);
        $this->assertNotEmpty($link->source_id);
        $this->assertNotEmpty($link->target_id);
        $this->assertNotEmpty($link->context_id);
    }
}

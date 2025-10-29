<?php

namespace Tests\Unit\Models;

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for Tag factory.
 */
class TagFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_valid_tag(): void
    {
        $tag = Tag::factory()->create();

        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertNotEmpty($tag->id);
        $this->assertNotEmpty($tag->internal_name);
        $this->assertNotEmpty($tag->backward_compatibility);
        $this->assertNotEmpty($tag->description);
    }
}

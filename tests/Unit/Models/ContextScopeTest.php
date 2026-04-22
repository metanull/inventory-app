<?php

namespace Tests\Unit\Models;

use App\Models\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for Context model scopes.
 */
class ContextScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_scope_default_returns_default_context(): void
    {
        $default = Context::factory()->default()->create();
        $nonDefault1 = Context::factory()->create(['is_default' => false]);
        $nonDefault2 = Context::factory()->create(['is_default' => false]);

        $result = Context::default()->first();

        $this->assertNotNull($result);
        $this->assertEquals($default->id, $result->id);
        $this->assertTrue($result->is_default);
    }

    public function test_scope_excluding_ids_excludes_given_ids(): void
    {
        $ctx1 = Context::factory()->create(['is_default' => false]);
        $ctx2 = Context::factory()->create(['is_default' => false]);
        $ctx3 = Context::factory()->create(['is_default' => false]);

        $results = Context::excludingIds([$ctx1->id, $ctx2->id])->get();

        $this->assertFalse($results->contains('id', $ctx1->id));
        $this->assertFalse($results->contains('id', $ctx2->id));
        $this->assertTrue($results->contains('id', $ctx3->id));
    }

    public function test_scope_excluding_ids_with_empty_array_returns_all(): void
    {
        Context::factory()->count(3)->create(['is_default' => false]);

        $results = Context::excludingIds([])->get();

        $this->assertCount(3, $results);
    }
}

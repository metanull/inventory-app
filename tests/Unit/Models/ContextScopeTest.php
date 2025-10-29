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
}

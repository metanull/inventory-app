<?php

namespace Tests\Unit\Models;

use App\Models\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for Context factory.
 */
class ContextFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_factory_creates_valid_context(): void
    {
        $context = Context::factory()->create();

        $this->assertInstanceOf(Context::class, $context);
        $this->assertNotEmpty($context->id);
        $this->assertNotEmpty($context->internal_name);
        $this->assertFalse($context->is_default);
    }

    public function test_factory_with_is_default_creates_default_context(): void
    {
        $context = Context::factory()->withIsDefault()->create();

        $this->assertTrue($context->is_default);
    }

    public function test_factory_default_method_creates_default_context(): void
    {
        $context = Context::factory()->default()->create();

        $this->assertTrue($context->is_default);
    }
}

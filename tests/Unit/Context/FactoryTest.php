<?php

namespace Tests\Unit\Context;

use App\Models\Context;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Factory: test that the factory creates a valid Context.
     */
    public function test_factory()
    {
        $context = Context::factory()->create();
        $this->assertInstanceOf(Context::class, $context);
        $this->assertNotNull($context->internal_name);
        $this->assertNotNull($context->backward_compatibility);
        $this->assertFalse($context->is_default);
    }

    /**
     * Factory: test that the factory can create a Context with is_default set to true.
     */
    public function test_factory_with_is_default()
    {
        $context = Context::factory()->withIsDefault()->create();
        $this->assertTrue($context->is_default);
    }

    public function test_factory_creates_a_row_in_database_without_is_default(): void
    {
        $context = Context::factory()->create();

        $this->assertDatabaseHas('contexts', [
            'id' => $context->id,
            'internal_name' => $context->internal_name,
            'backward_compatibility' => $context->backward_compatibility,
            'is_default' => false,
        ]);
    }

    public function test_factory_creates_a_row_in_database_with_is_default(): void
    {
        $context = Context::factory()->withIsDefault()->create();
        $this->assertDatabaseHas('contexts', [
            'id' => $context->id,
            'internal_name' => $context->internal_name,
            'backward_compatibility' => $context->backward_compatibility,
            'is_default' => true,
        ]);
    }

    public function test_model_scope_default(): void
    {
        $defaultContext = Context::factory()->withIsDefault()->create();
        $otherContext = Context::factory()->create();

        $this->assertEquals($defaultContext->id, Context::default()->first()->id);
        $this->assertNotEquals($otherContext->id, Context::default()->first()->id);
    }

    public function test_model_method_set_default(): void
    {
        $context1 = Context::factory()->create();
        $context2 = Context::factory()->create();
        $this->assertFalse($context1->fresh()->is_default);
        $this->assertFalse($context2->fresh()->is_default);
        $context1->setDefault();
        $this->assertTrue($context1->fresh()->is_default);
        $this->assertFalse($context2->fresh()->is_default);
        $context2->setDefault();
        $this->assertFalse($context1->fresh()->is_default);
        $this->assertTrue($context2->fresh()->is_default);
        $this->assertEquals(1, Context::where('is_default', true)->count());
    }
}

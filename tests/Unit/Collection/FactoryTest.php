<?php

namespace Tests\Unit\Collection;

use App\Models\Collection;
use App\Models\Context;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test that Collection factory creates valid collections.
     */
    public function test_collection_factory_creates_valid_collection(): void
    {
        // Create necessary dependencies
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        // Create a collection using the factory
        $collection = Collection::factory()
            ->withLanguage($language->id)
            ->withContext($context->id)
            ->create();

        // Assert the collection was created properly
        $this->assertDatabaseHas('collections', [
            'id' => $collection->id,
            'internal_name' => $collection->internal_name,
            'language_id' => $language->id,
            'context_id' => $context->id,
        ]);

        // Assert relationships work
        $this->assertEquals($language->id, $collection->language->id);
        $this->assertEquals($context->id, $collection->context->id);

        // Assert required fields are not null
        $this->assertNotNull($collection->id);
        $this->assertNotNull($collection->internal_name);
        $this->assertNotNull($collection->language_id);
        $this->assertNotNull($collection->context_id);
        $this->assertNotNull($collection->created_at);
        $this->assertNotNull($collection->updated_at);
    }

    /**
     * Test that Collection factory generates unique internal names.
     */
    public function test_collection_factory_generates_unique_internal_names(): void
    {
        // Create two collections
        $collection1 = Collection::factory()->create();
        $collection2 = Collection::factory()->create();

        // Assert internal names are different
        $this->assertNotEquals($collection1->internal_name, $collection2->internal_name);
    }

    /**
     * Test Collection factory with default language.
     */
    public function test_collection_factory_with_default_language(): void
    {
        // Create a default language
        $defaultLanguage = Language::factory()->create(['is_default' => true]);
        Language::factory()->count(2)->create(['is_default' => false]);

        // Create collection with default language
        $collection = Collection::factory()->withDefaultLanguage()->create();

        // Assert it uses the default language
        $this->assertEquals($defaultLanguage->id, $collection->language_id);
    }

    /**
     * Test Collection factory with default context.
     */
    public function test_collection_factory_with_default_context(): void
    {
        // Create a default context
        $defaultContext = Context::factory()->create(['is_default' => true]);
        Context::factory()->count(2)->create(['is_default' => false]);

        // Create collection with default context
        $collection = Collection::factory()->withDefaultContext()->create();

        // Assert it uses the default context
        $this->assertEquals($defaultContext->id, $collection->context_id);
    }

    /**
     * Test Collection factory creates valid UUID.
     */
    public function test_collection_factory_creates_valid_uuid(): void
    {
        $collection = Collection::factory()->create();

        // Assert ID is a valid UUID format
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $collection->id
        );
    }

    /**
     * Test Collection factory with backward compatibility.
     */
    public function test_collection_factory_with_backward_compatibility(): void
    {
        $collection = Collection::factory()->create([
            'backward_compatibility' => 'legacy-id-123',
        ]);

        $this->assertEquals('legacy-id-123', $collection->backward_compatibility);
    }

    /**
     * Test Collection factory without backward compatibility.
     */
    public function test_collection_factory_without_backward_compatibility(): void
    {
        $collection = Collection::factory()->create([
            'backward_compatibility' => null,
        ]);

        $this->assertNull($collection->backward_compatibility);
    }

    /**
     * Test that Collection factory respects constraints.
     */
    public function test_collection_factory_respects_constraints(): void
    {
        $collection = Collection::factory()->create();

        // Test internal_name is not empty and follows the expected format
        $this->assertNotEmpty($collection->internal_name);
        $this->assertIsString($collection->internal_name);

        // Test language_id follows the expected format (3 chars)
        $this->assertIsString($collection->language_id);
        $this->assertEquals(3, strlen($collection->language->id));

        // Test context_id is a valid UUID
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $collection->context_id
        );
    }
}

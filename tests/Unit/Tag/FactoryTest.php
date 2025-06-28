<?php

namespace Tests\Unit\Tag;

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Factory: test that the factory creates a valid Tag.
     */
    public function test_factory()
    {
        $tag = Tag::factory()->create();
        $this->assertInstanceOf(Tag::class, $tag);
        $this->assertNotNull($tag->internal_name);
        $this->assertNotNull($tag->backward_compatibility);
        $this->assertNotNull($tag->description);
    }

    public function test_factory_creates_a_row_in_database(): void
    {
        $tag = Tag::factory()->create();

        $this->assertDatabaseHas('tags', [
            'id' => $tag->id,
            'internal_name' => $tag->internal_name,
            'backward_compatibility' => $tag->backward_compatibility,
            'description' => $tag->description,
        ]);
    }

    public function test_factory_complies_with_constraints(): void
    {
        $tag = Tag::factory()->create();

        // Test internal_name is required
        $this->assertNotNull($tag->internal_name);
        $this->assertIsString($tag->internal_name);

        // Test backward_compatibility is nullable
        $this->assertTrue(is_null($tag->backward_compatibility) || is_string($tag->backward_compatibility));

        // Test description is required
        $this->assertNotNull($tag->description);
        $this->assertIsString($tag->description);

        // Test UUID id
        $this->assertIsString($tag->id);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $tag->id);
    }

    public function test_factory_creates_unique_internal_names(): void
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $this->assertNotEquals($tag1->internal_name, $tag2->internal_name);
    }

    public function test_factory_generates_words_for_description(): void
    {
        $tag = Tag::factory()->create();
        
        // The description should contain multiple words (faker->words(5))
        $this->assertGreaterThan(10, strlen($tag->description)); // Should be more than just a single word
        $this->assertStringContainsString(' ', $tag->description); // Should contain spaces between words
    }
}

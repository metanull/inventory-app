<?php

namespace Tests\Feature\Api\Gallery;

use App\Models\Gallery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Gallery Index Test
 *
 * Tests the gallery listing/index API endpoint.
 * Verifies proper data retrieval and response formatting.
 */
class IndexTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /**
     * Test that authenticated users can access gallery index.
     */
    public function test_authenticated_user_can_access_gallery_index(): void
    {
        $response = $this->getJson(route('gallery.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [],
        ]);
    }

    /**
     * Test gallery index returns empty array when no galleries exist.
     */
    public function test_gallery_index_returns_empty_when_no_galleries(): void
    {
        $response = $this->getJson(route('gallery.index'));

        $response->assertOk();
        $response->assertJson([
            'data' => [],
        ]);
    }

    /**
     * Test gallery index returns galleries with proper structure.
     */
    public function test_gallery_index_returns_galleries_with_proper_structure(): void
    {
        $gallery = Gallery::factory()->create();

        $response = $this->getJson(route('gallery.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'internal_name',
                    'backward_compatibility',
                    'created_at',
                    'updated_at',
                    'translations',
                    'partners',
                    'items',
                    'details',
                    'items_count',
                    'details_count',
                    'total_content_count',
                    'partners_count',
                    'translations_count',
                ],
            ],
        ]);

        $response->assertJsonPath('data.0.id', $gallery->id);
        $response->assertJsonPath('data.0.internal_name', $gallery->internal_name);
    }

    /**
     * Test gallery index includes relationships when loaded.
     */
    public function test_gallery_index_includes_relationships(): void
    {
        $gallery = Gallery::factory()->create();

        $response = $this->getJson(route('gallery.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'translations',
                    'partners',
                    'items',
                    'details',
                ],
            ],
        ]);
    }

    /**
     * Test gallery index returns multiple galleries.
     */
    public function test_gallery_index_returns_multiple_galleries(): void
    {
        $galleries = Gallery::factory()->count(3)->create();

        $response = $this->getJson(route('gallery.index'));

        $response->assertOk();
        $response->assertJsonCount(3, 'data');

        foreach ($galleries as $index => $gallery) {
            $response->assertJsonPath("data.{$index}.id", $gallery->id);
            $response->assertJsonPath("data.{$index}.internal_name", $gallery->internal_name);
        }
    }

    /**
     * Test gallery index includes computed attributes.
     */
    public function test_gallery_index_includes_computed_attributes(): void
    {
        $gallery = Gallery::factory()->create();

        $response = $this->getJson(route('gallery.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'items_count',
                    'details_count',
                    'total_content_count',
                    'partners_count',
                    'translations_count',
                ],
            ],
        ]);

        // Verify counts are integers
        $response->assertJsonPath('data.0.items_count', 0);
        $response->assertJsonPath('data.0.details_count', 0);
        $response->assertJsonPath('data.0.total_content_count', 0);
        $response->assertJsonPath('data.0.partners_count', 0);
        $response->assertJsonPath('data.0.translations_count', 0);
    }
}

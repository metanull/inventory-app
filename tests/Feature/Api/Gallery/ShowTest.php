<?php

namespace Tests\Feature\Api\Gallery;

use App\Models\Gallery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Gallery Show Test
 *
 * Tests the gallery show API endpoint for displaying single galleries.
 * Verifies proper data retrieval and response formatting.
 */
class ShowTest extends TestCase
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
     * Test that authenticated users can view a specific gallery.
     */
    public function test_authenticated_user_can_view_gallery(): void
    {
        $gallery = Gallery::factory()->create();

        $response = $this->getJson(route('gallery.show', $gallery));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
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
        ]);
    }

    /**
     * Test gallery show returns correct gallery data.
     */
    public function test_gallery_show_returns_correct_data(): void
    {
        $gallery = Gallery::factory()->create();

        $response = $this->getJson(route('gallery.show', $gallery));

        $response->assertOk();
        $response->assertJsonPath('data.id', $gallery->id);
        $response->assertJsonPath('data.internal_name', $gallery->internal_name);
        $response->assertJsonPath('data.backward_compatibility', $gallery->backward_compatibility);
    }

    /**
     * Test gallery show includes relationships.
     */
    public function test_gallery_show_includes_relationships(): void
    {
        $gallery = Gallery::factory()->create();

        $response = $this->getJson(route('gallery.show', $gallery));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'translations',
                'partners',
                'items',
                'details',
            ],
        ]);
    }

    /**
     * Test gallery show includes computed attributes.
     */
    public function test_gallery_show_includes_computed_attributes(): void
    {
        $gallery = Gallery::factory()->create();

        $response = $this->getJson(route('gallery.show', $gallery));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'items_count',
                'details_count',
                'total_content_count',
                'partners_count',
                'translations_count',
            ],
        ]);

        // Verify counts are integers
        $response->assertJsonPath('data.items_count', 0);
        $response->assertJsonPath('data.details_count', 0);
        $response->assertJsonPath('data.total_content_count', 0);
        $response->assertJsonPath('data.partners_count', 0);
        $response->assertJsonPath('data.translations_count', 0);
    }

    /**
     * Test gallery show returns 404 for non-existent gallery.
     */
    public function test_gallery_show_returns_404_for_non_existent_gallery(): void
    {
        $response = $this->getJson(route('gallery.show', ['gallery' => 'non-existent-id']));

        $response->assertNotFound();
    }

    /**
     * Test gallery show with gallery that has backward compatibility.
     */
    public function test_gallery_show_with_backward_compatibility(): void
    {
        $gallery = Gallery::factory()->withBackwardCompatibility()->create();

        $response = $this->getJson(route('gallery.show', $gallery));

        $response->assertOk();
        $response->assertJsonPath('data.id', $gallery->id);
        $response->assertJsonPath('data.backward_compatibility', $gallery->backward_compatibility);
        $this->assertNotNull($gallery->backward_compatibility);
    }

    /**
     * Test gallery show response format is consistent.
     */
    public function test_gallery_show_response_format_is_consistent(): void
    {
        $gallery = Gallery::factory()->create();

        $response = $this->getJson(route('gallery.show', $gallery));

        $response->assertOk();

        // Verify the response structure matches expected format
        $responseData = $response->json('data');

        $this->assertIsString($responseData['id']);
        $this->assertIsString($responseData['internal_name']);
        $this->assertIsArray($responseData['translations']);
        $this->assertIsArray($responseData['partners']);
        $this->assertIsArray($responseData['items']);
        $this->assertIsArray($responseData['details']);
        $this->assertIsInt($responseData['items_count']);
        $this->assertIsInt($responseData['details_count']);
        $this->assertIsInt($responseData['total_content_count']);
        $this->assertIsInt($responseData['partners_count']);
        $this->assertIsInt($responseData['translations_count']);
    }
}

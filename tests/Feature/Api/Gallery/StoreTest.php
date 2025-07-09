<?php

namespace Tests\Feature\Api\Gallery;

use App\Models\Gallery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Gallery Store Test
 *
 * Tests the gallery creation API endpoint.
 * Verifies proper validation and gallery creation functionality.
 */
class StoreTest extends TestCase
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
     * Test that authenticated users can create galleries.
     */
    public function test_authenticated_user_can_create_gallery(): void
    {
        $galleryData = Gallery::factory()->make()->toArray();

        $response = $this->postJson(route('gallery.store'), $galleryData);

        $response->assertCreated();
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

        $this->assertDatabaseHas('galleries', [
            'internal_name' => $galleryData['internal_name'],
        ]);
    }

    /**
     * Test gallery creation with valid data.
     */
    public function test_gallery_creation_with_valid_data(): void
    {
        $galleryData = [
            'internal_name' => 'test-gallery-'.$this->faker->slug(2),
            'backward_compatibility' => null,
        ];

        $response = $this->postJson(route('gallery.store'), $galleryData);

        $response->assertCreated();
        $response->assertJsonPath('data.internal_name', $galleryData['internal_name']);
        $response->assertJsonPath('data.backward_compatibility', null);

        $this->assertDatabaseHas('galleries', [
            'internal_name' => $galleryData['internal_name'],
            'backward_compatibility' => null,
        ]);
    }

    /**
     * Test gallery creation with backward compatibility.
     */
    public function test_gallery_creation_with_backward_compatibility(): void
    {
        $galleryData = [
            'internal_name' => 'test-gallery-'.$this->faker->slug(2),
            'backward_compatibility' => $this->faker->uuid(),
        ];

        $response = $this->postJson(route('gallery.store'), $galleryData);

        $response->assertCreated();
        $response->assertJsonPath('data.internal_name', $galleryData['internal_name']);
        $response->assertJsonPath('data.backward_compatibility', $galleryData['backward_compatibility']);

        $this->assertDatabaseHas('galleries', [
            'internal_name' => $galleryData['internal_name'],
            'backward_compatibility' => $galleryData['backward_compatibility'],
        ]);
    }

    /**
     * Test gallery creation requires internal_name.
     */
    public function test_gallery_creation_requires_internal_name(): void
    {
        $galleryData = Gallery::factory()->make()->except(['internal_name']);

        $response = $this->postJson(route('gallery.store'), $galleryData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    /**
     * Test gallery creation requires unique internal_name.
     */
    public function test_gallery_creation_requires_unique_internal_name(): void
    {
        $existingGallery = Gallery::factory()->create();

        $galleryData = [
            'internal_name' => $existingGallery->internal_name,
        ];

        $response = $this->postJson(route('gallery.store'), $galleryData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    /**
     * Test gallery creation with invalid internal_name format.
     */
    public function test_gallery_creation_with_invalid_internal_name_format(): void
    {
        $galleryData = [
            'internal_name' => '', // Empty string
        ];

        $response = $this->postJson(route('gallery.store'), $galleryData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    /**
     * Test gallery creation with too long internal_name.
     */
    public function test_gallery_creation_with_too_long_internal_name(): void
    {
        $galleryData = [
            'internal_name' => str_repeat('a', 256), // Exceeds 255 character limit
        ];

        $response = $this->postJson(route('gallery.store'), $galleryData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    /**
     * Test gallery creation ignores unknown fields.
     */
    public function test_gallery_creation_ignores_unknown_fields(): void
    {
        $galleryData = [
            'internal_name' => 'test-gallery-'.$this->faker->slug(2),
            'unknown_field' => 'unknown_value',
            'another_unknown' => 123,
        ];

        $response = $this->postJson(route('gallery.store'), $galleryData);

        $response->assertCreated();
        $response->assertJsonPath('data.internal_name', $galleryData['internal_name']);

        $this->assertDatabaseHas('galleries', [
            'internal_name' => $galleryData['internal_name'],
        ]);

        // Verify unknown fields are not stored
        $this->assertDatabaseMissing('galleries', [
            'unknown_field' => 'unknown_value',
        ]);
    }

    /**
     * Test gallery creation response includes relationships.
     */
    public function test_gallery_creation_response_includes_relationships(): void
    {
        $galleryData = Gallery::factory()->make()->toArray();

        $response = $this->postJson(route('gallery.store'), $galleryData);

        $response->assertCreated();
        $response->assertJsonStructure([
            'data' => [
                'translations',
                'partners',
                'items',
                'details',
            ],
        ]);

        // Initially empty relationships
        $response->assertJsonPath('data.translations', []);
        $response->assertJsonPath('data.partners', []);
        $response->assertJsonPath('data.items', []);
        $response->assertJsonPath('data.details', []);
    }

    /**
     * Test that created gallery has proper UUID.
     */
    public function test_created_gallery_has_proper_uuid(): void
    {
        $galleryData = Gallery::factory()->make()->toArray();

        $response = $this->postJson(route('gallery.store'), $galleryData);

        $response->assertCreated();

        $galleryId = $response->json('data.id');
        $this->assertIsString($galleryId);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $galleryId);
    }
}

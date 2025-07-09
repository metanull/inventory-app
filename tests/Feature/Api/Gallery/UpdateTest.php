<?php

namespace Tests\Feature\Api\Gallery;

use App\Models\Gallery;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Gallery Update Test
 *
 * Tests the gallery update API endpoint.
 * Verifies proper validation and gallery update functionality.
 */
class UpdateTest extends TestCase
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
     * Test that authenticated users can update galleries.
     */
    public function test_authenticated_user_can_update_gallery(): void
    {
        $gallery = Gallery::factory()->create();
        $updateData = Gallery::factory()->make()->toArray();

        $response = $this->putJson(route('gallery.update', $gallery), $updateData);

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
     * Test gallery update with valid data.
     */
    public function test_gallery_update_with_valid_data(): void
    {
        $gallery = Gallery::factory()->create();
        $updateData = [
            'internal_name' => 'updated-gallery-'.$this->faker->slug(2),
        ];

        $response = $this->putJson(route('gallery.update', $gallery), $updateData);

        $response->assertOk();
        $response->assertJsonPath('data.id', $gallery->id);
        $response->assertJsonPath('data.internal_name', $updateData['internal_name']);

        $this->assertDatabaseHas('galleries', [
            'id' => $gallery->id,
            'internal_name' => $updateData['internal_name'],
        ]);
    }

    /**
     * Test gallery partial update.
     */
    public function test_gallery_partial_update(): void
    {
        $gallery = Gallery::factory()->create();
        $originalName = $gallery->internal_name;

        $updateData = [
            'backward_compatibility' => $this->faker->uuid(),
        ];

        $response = $this->putJson(route('gallery.update', $gallery), $updateData);

        $response->assertOk();
        $response->assertJsonPath('data.id', $gallery->id);
        $response->assertJsonPath('data.internal_name', $originalName); // Should remain unchanged
        $response->assertJsonPath('data.backward_compatibility', $updateData['backward_compatibility']);

        $this->assertDatabaseHas('galleries', [
            'id' => $gallery->id,
            'internal_name' => $originalName,
            'backward_compatibility' => $updateData['backward_compatibility'],
        ]);
    }

    /**
     * Test gallery update requires unique internal_name.
     */
    public function test_gallery_update_requires_unique_internal_name(): void
    {
        $gallery1 = Gallery::factory()->create();
        $gallery2 = Gallery::factory()->create();

        $updateData = [
            'internal_name' => $gallery2->internal_name, // Try to use another gallery's name
        ];

        $response = $this->putJson(route('gallery.update', $gallery1), $updateData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    /**
     * Test gallery can be updated with its own internal_name.
     */
    public function test_gallery_can_be_updated_with_own_internal_name(): void
    {
        $gallery = Gallery::factory()->create();

        $updateData = [
            'internal_name' => $gallery->internal_name, // Same name should be allowed
            'backward_compatibility' => $this->faker->uuid(),
        ];

        $response = $this->putJson(route('gallery.update', $gallery), $updateData);

        $response->assertOk();
        $response->assertJsonPath('data.id', $gallery->id);
        $response->assertJsonPath('data.internal_name', $updateData['internal_name']);
        $response->assertJsonPath('data.backward_compatibility', $updateData['backward_compatibility']);
    }

    /**
     * Test gallery update with invalid internal_name format.
     */
    public function test_gallery_update_with_invalid_internal_name_format(): void
    {
        $gallery = Gallery::factory()->create();

        $updateData = [
            'internal_name' => '', // Empty string
        ];

        $response = $this->putJson(route('gallery.update', $gallery), $updateData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    /**
     * Test gallery update with too long internal_name.
     */
    public function test_gallery_update_with_too_long_internal_name(): void
    {
        $gallery = Gallery::factory()->create();

        $updateData = [
            'internal_name' => str_repeat('a', 256), // Exceeds 255 character limit
        ];

        $response = $this->putJson(route('gallery.update', $gallery), $updateData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    /**
     * Test gallery update ignores unknown fields.
     */
    public function test_gallery_update_ignores_unknown_fields(): void
    {
        $gallery = Gallery::factory()->create();
        $originalName = $gallery->internal_name;

        $updateData = [
            'unknown_field' => 'unknown_value',
            'another_unknown' => 123,
        ];

        $response = $this->putJson(route('gallery.update', $gallery), $updateData);

        $response->assertOk();
        $response->assertJsonPath('data.internal_name', $originalName); // Should remain unchanged

        // Verify unknown fields are not stored
        $this->assertDatabaseHas('galleries', [
            'id' => $gallery->id,
            'internal_name' => $originalName,
        ]);
    }

    /**
     * Test gallery update returns 404 for non-existent gallery.
     */
    public function test_gallery_update_returns_404_for_non_existent_gallery(): void
    {
        $updateData = [
            'internal_name' => 'updated-gallery',
        ];

        $response = $this->putJson(route('gallery.update', ['gallery' => 'non-existent-id']), $updateData);

        $response->assertNotFound();
    }

    /**
     * Test gallery update response includes relationships.
     */
    public function test_gallery_update_response_includes_relationships(): void
    {
        $gallery = Gallery::factory()->create();
        $updateData = [
            'internal_name' => 'updated-gallery-'.$this->faker->slug(2),
        ];

        $response = $this->putJson(route('gallery.update', $gallery), $updateData);

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
     * Test gallery update preserves timestamps correctly.
     */
    public function test_gallery_update_preserves_timestamps_correctly(): void
    {
        $gallery = Gallery::factory()->create();
        $originalCreatedAt = $gallery->created_at;

        // Wait a moment to ensure updated_at will be different
        sleep(1);

        $updateData = [
            'internal_name' => 'updated-gallery-'.$this->faker->slug(2),
        ];

        $response = $this->putJson(route('gallery.update', $gallery), $updateData);

        $response->assertOk();

        $gallery->refresh();

        // created_at should remain the same
        $this->assertEquals($originalCreatedAt->timestamp, $gallery->created_at->timestamp);

        // updated_at should be newer
        $this->assertGreaterThan($originalCreatedAt->timestamp, $gallery->updated_at->timestamp);
    }
}

<?php

namespace Tests\Feature\Api\Gallery;

use Tests\TestCase;

/**
 * Gallery Anonymous Access Test
 *
 * Tests unauthorized access scenarios for Gallery API endpoints.
 * Ensures proper authentication requirements are enforced.
 */
class AnonymousTest extends TestCase
{
    /**
     * Set up test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Anonymous test - no user authentication
    }

    /**
     * Test that anonymous users cannot access gallery index.
     */
    public function test_anonymous_cannot_access_gallery_index(): void
    {
        $response = $this->getJson(route('gallery.index'));

        $response->assertStatus(401);
    }

    /**
     * Test that anonymous users cannot view a gallery.
     */
    public function test_anonymous_cannot_view_gallery(): void
    {
        $response = $this->getJson(route('gallery.show', ['gallery' => 'test-gallery']));

        $response->assertStatus(401);
    }

    /**
     * Test that anonymous users cannot create galleries.
     */
    public function test_anonymous_cannot_create_gallery(): void
    {
        $galleryData = [
            'internal_name' => 'test-gallery',
        ];

        $response = $this->postJson(route('gallery.store'), $galleryData);

        $response->assertStatus(401);
    }

    /**
     * Test that anonymous users cannot update galleries.
     */
    public function test_anonymous_cannot_update_gallery(): void
    {
        $galleryData = [
            'internal_name' => 'updated-gallery',
        ];

        $response = $this->putJson(route('gallery.update', ['gallery' => 'test-gallery']), $galleryData);

        $response->assertStatus(401);
    }

    /**
     * Test that anonymous users cannot delete galleries.
     */
    public function test_anonymous_cannot_delete_gallery(): void
    {
        $response = $this->deleteJson(route('gallery.destroy', ['gallery' => 'test-gallery']));

        $response->assertStatus(401);
    }
}

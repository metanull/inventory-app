<?php

namespace Tests\Feature\Api\Tag;

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Authentication: index requires authentication.
     */
    public function test_index_requires_authentication()
    {
        $response = $this->getJson(route('tag.index'));
        $response->assertUnauthorized();
    }

    /**
     * Authentication: show requires authentication.
     */
    public function test_show_requires_authentication()
    {
        $tag = Tag::factory()->create();

        $response = $this->getJson(route('tag.show', $tag));
        $response->assertUnauthorized();
    }

    /**
     * Authentication: store requires authentication.
     */
    public function test_store_requires_authentication()
    {
        $data = Tag::factory()->make()->except(['id']);

        $response = $this->postJson(route('tag.store'), $data);
        $response->assertUnauthorized();
    }

    /**
     * Authentication: update requires authentication.
     */
    public function test_update_requires_authentication()
    {
        $tag = Tag::factory()->create();
        $data = Tag::factory()->make()->except(['id']);

        $response = $this->putJson(route('tag.update', $tag), $data);
        $response->assertUnauthorized();
    }

    /**
     * Authentication: destroy requires authentication.
     */
    public function test_destroy_requires_authentication()
    {
        $tag = Tag::factory()->create();

        $response = $this->deleteJson(route('tag.destroy', $tag));
        $response->assertUnauthorized();
    }
}

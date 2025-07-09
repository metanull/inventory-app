<?php

namespace Tests\Feature\Api\Collection;

use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test that anonymous users cannot access collection index.
     */
    public function test_anonymous_cannot_access_collection_index(): void
    {
        Collection::factory()->count(3)->create();

        $response = $this->getJson(route('collection.index'));

        $response->assertStatus(401);
    }

    /**
     * Test that anonymous users cannot access collection show.
     */
    public function test_anonymous_cannot_access_collection_show(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->getJson(route('collection.show', $collection));

        $response->assertStatus(401);
    }

    /**
     * Test that anonymous users cannot create collections.
     */
    public function test_anonymous_cannot_create_collection(): void
    {
        $collectionData = Collection::factory()->make()->toArray();

        $response = $this->postJson(route('collection.store'), $collectionData);

        $response->assertStatus(401);
    }

    /**
     * Test that anonymous users cannot update collections.
     */
    public function test_anonymous_cannot_update_collection(): void
    {
        $collection = Collection::factory()->create();
        $updateData = ['internal_name' => 'updated_name'];

        $response = $this->putJson(route('collection.update', $collection), $updateData);

        $response->assertStatus(401);
    }

    /**
     * Test that anonymous users cannot delete collections.
     */
    public function test_anonymous_cannot_delete_collection(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->deleteJson(route('collection.destroy', $collection));

        $response->assertStatus(401);
    }
}

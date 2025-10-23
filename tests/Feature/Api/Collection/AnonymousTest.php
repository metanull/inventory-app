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

    /**
     * Test that anonymous users cannot attach item to collection.
     */
    public function test_anonymous_cannot_attach_item(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->postJson(route('collection.attachItem', $collection), [
            'item_id' => 'f47ac10b-58cc-4372-a567-0e02b2c3d479',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test that anonymous users cannot attach items to collection.
     */
    public function test_anonymous_cannot_attach_items(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->postJson(route('collection.attachItems', $collection), [
            'item_ids' => ['f47ac10b-58cc-4372-a567-0e02b2c3d479'],
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test that anonymous users cannot detach item from collection.
     */
    public function test_anonymous_cannot_detach_item(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->deleteJson(route('collection.detachItem', $collection), [
            'item_id' => 'f47ac10b-58cc-4372-a567-0e02b2c3d479',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test that anonymous users cannot detach items from collection.
     */
    public function test_anonymous_cannot_detach_items(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->deleteJson(route('collection.detachItems', $collection), [
            'item_ids' => ['f47ac10b-58cc-4372-a567-0e02b2c3d479'],
        ]);

        $response->assertStatus(401);
    }
}

<?php

namespace Tests\Web\Pages;

use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

/**
 * Tests for Collection parent-child relationship operations
 */
class CollectionParentChildTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_can_set_parent_for_collection(): void
    {
        $parent = Collection::factory()->create();
        $child = Collection::factory()->create(['parent_id' => null]);

        $response = $this->post(route('collections.setParent', $child), [
            'parent_id' => $parent->id,
        ]);

        $response->assertRedirect(route('collections.show', $child));
        $this->assertDatabaseHas('collections', [
            'id' => $child->id,
            'parent_id' => $parent->id,
        ]);
    }

    public function test_cannot_set_collection_as_its_own_parent(): void
    {
        $collection = Collection::factory()->create();

        $response = $this->post(route('collections.setParent', $collection), [
            'parent_id' => $collection->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('parent_id');
        $this->assertDatabaseHas('collections', [
            'id' => $collection->id,
            'parent_id' => null,
        ]);
    }

    public function test_cannot_create_circular_parent_relationship(): void
    {
        // Create chain: grandparent -> parent -> child
        $grandparent = Collection::factory()->create(['parent_id' => null]);
        $parent = Collection::factory()->create(['parent_id' => $grandparent->id]);
        $child = Collection::factory()->create(['parent_id' => $parent->id]);

        // Try to set grandparent's parent to child (would create circular reference)
        $response = $this->post(route('collections.setParent', $grandparent), [
            'parent_id' => $child->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('parent_id');
        $this->assertDatabaseHas('collections', [
            'id' => $grandparent->id,
            'parent_id' => null,
        ]);
    }

    public function test_can_remove_parent_from_collection(): void
    {
        $parent = Collection::factory()->create();
        $child = Collection::factory()->create(['parent_id' => $parent->id]);

        $response = $this->delete(route('collections.removeParent', $child));

        $response->assertRedirect(route('collections.show', $child));
        $this->assertDatabaseHas('collections', [
            'id' => $child->id,
            'parent_id' => null,
        ]);
    }

    public function test_set_parent_requires_authentication(): void
    {
        $parent = Collection::factory()->create();
        $child = Collection::factory()->create();

        auth()->logout();

        $response = $this->post(route('collections.setParent', $child), [
            'parent_id' => $parent->id,
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_remove_parent_requires_authentication(): void
    {
        $parent = Collection::factory()->create();
        $child = Collection::factory()->create(['parent_id' => $parent->id]);

        auth()->logout();

        $response = $this->delete(route('collections.removeParent', $child));

        $response->assertRedirect(route('login'));
    }

    public function test_set_parent_requires_valid_parent_id(): void
    {
        $child = Collection::factory()->create();

        $response = $this->post(route('collections.setParent', $child), [
            'parent_id' => 'invalid-uuid',
        ]);

        $response->assertSessionHasErrors('parent_id');
    }

    public function test_set_parent_requires_existing_parent(): void
    {
        $child = Collection::factory()->create();

        $response = $this->post(route('collections.setParent', $child), [
            'parent_id' => '00000000-0000-0000-0000-000000000000',
        ]);

        $response->assertSessionHasErrors('parent_id');
    }
}

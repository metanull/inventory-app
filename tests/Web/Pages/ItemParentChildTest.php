<?php

namespace Tests\Web\Pages;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

/**
 * Tests for Item parent-child relationship operations
 */
class ItemParentChildTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_can_set_parent_for_item(): void
    {
        $parent = Item::factory()->create(['type' => 'object']);
        $child = Item::factory()->create(['type' => 'detail', 'parent_id' => null]);

        $response = $this->post(route('items.setParent', $child), [
            'parent_id' => $parent->id,
        ]);

        $response->assertRedirect(route('items.show', $child));
        $this->assertDatabaseHas('items', [
            'id' => $child->id,
            'parent_id' => $parent->id,
        ]);
    }

    public function test_cannot_set_item_as_its_own_parent(): void
    {
        $item = Item::factory()->create(['type' => 'detail']);

        $response = $this->post(route('items.setParent', $item), [
            'parent_id' => $item->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('parent_id');
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'parent_id' => null,
        ]);
    }

    public function test_cannot_create_circular_parent_relationship(): void
    {
        // Create chain: grandparent -> parent -> child
        $grandparent = Item::factory()->create(['type' => 'object', 'parent_id' => null]);
        $parent = Item::factory()->create(['type' => 'object', 'parent_id' => $grandparent->id]);
        $child = Item::factory()->create(['type' => 'detail', 'parent_id' => $parent->id]);

        // Try to set grandparent's parent to child (would create circular reference)
        $response = $this->post(route('items.setParent', $grandparent), [
            'parent_id' => $child->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('parent_id');
        $this->assertDatabaseHas('items', [
            'id' => $grandparent->id,
            'parent_id' => null,
        ]);
    }

    public function test_can_remove_parent_from_item(): void
    {
        $parent = Item::factory()->create(['type' => 'object']);
        $child = Item::factory()->create(['type' => 'detail', 'parent_id' => $parent->id]);

        $response = $this->delete(route('items.removeParent', $child));

        $response->assertRedirect(route('items.show', $child));
        $this->assertDatabaseHas('items', [
            'id' => $child->id,
            'parent_id' => null,
        ]);
    }

    public function test_set_parent_requires_authentication(): void
    {
        $parent = Item::factory()->create();
        $child = Item::factory()->create();

        auth()->logout();

        $response = $this->post(route('items.setParent', $child), [
            'parent_id' => $parent->id,
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_remove_parent_requires_authentication(): void
    {
        $parent = Item::factory()->create();
        $child = Item::factory()->create(['parent_id' => $parent->id]);

        auth()->logout();

        $response = $this->delete(route('items.removeParent', $child));

        $response->assertRedirect(route('login'));
    }

    public function test_set_parent_requires_valid_parent_id(): void
    {
        $child = Item::factory()->create();

        $response = $this->post(route('items.setParent', $child), [
            'parent_id' => 'invalid-uuid',
        ]);

        $response->assertSessionHasErrors('parent_id');
    }

    public function test_set_parent_requires_existing_parent(): void
    {
        $child = Item::factory()->create();

        $response = $this->post(route('items.setParent', $child), [
            'parent_id' => '00000000-0000-0000-0000-000000000000',
        ]);

        $response->assertSessionHasErrors('parent_id');
    }
}

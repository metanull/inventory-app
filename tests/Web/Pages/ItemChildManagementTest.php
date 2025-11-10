<?php

namespace Tests\Web\Pages;

use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

/**
 * Tests for Item addChild and removeChild operations
 */
class ItemChildManagementTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_can_add_child_to_item(): void
    {
        $parent = Item::factory()->create(['type' => 'object']);
        $child = Item::factory()->create(['type' => 'detail', 'parent_id' => null]);

        $response = $this->post(route('items.addChild', $parent), [
            'child_id' => $child->id,
        ]);

        $response->assertRedirect(route('items.show', $parent));
        $this->assertDatabaseHas('items', [
            'id' => $child->id,
            'parent_id' => $parent->id,
        ]);
    }

    public function test_cannot_add_item_as_its_own_child(): void
    {
        $item = Item::factory()->create(['type' => 'object']);

        $response = $this->post(route('items.addChild', $item), [
            'child_id' => $item->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('child_id');
    }

    public function test_cannot_create_circular_child_relationship(): void
    {
        $grandparent = Item::factory()->create(['type' => 'object', 'parent_id' => null]);
        $parent = Item::factory()->create(['type' => 'object', 'parent_id' => $grandparent->id]);
        $child = Item::factory()->create(['type' => 'detail', 'parent_id' => $parent->id]);

        $response = $this->post(route('items.addChild', $child), [
            'child_id' => $grandparent->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('child_id');
    }

    public function test_can_remove_child_from_item(): void
    {
        $parent = Item::factory()->create(['type' => 'object']);
        $child = Item::factory()->create(['type' => 'detail', 'parent_id' => $parent->id]);

        $response = $this->delete(route('items.removeChild', [$parent, $child]));

        $response->assertRedirect(route('items.show', $parent));
        $this->assertDatabaseHas('items', [
            'id' => $child->id,
            'parent_id' => null,
        ]);
    }

    public function test_remove_child_requires_valid_relationship(): void
    {
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();

        $response = $this->delete(route('items.removeChild', [$item1, $item2]));

        $response->assertRedirect(route('items.show', $item1));
        $response->assertSessionHasErrors();
    }

    public function test_add_child_requires_authentication(): void
    {
        $parent = Item::factory()->create();
        $child = Item::factory()->create();

        auth()->logout();

        $response = $this->post(route('items.addChild', $parent), [
            'child_id' => $child->id,
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_remove_child_requires_authentication(): void
    {
        $parent = Item::factory()->create();
        $child = Item::factory()->create(['parent_id' => $parent->id]);

        auth()->logout();

        $response = $this->delete(route('items.removeChild', [$parent, $child]));

        $response->assertRedirect(route('login'));
    }
}

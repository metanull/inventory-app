<?php

namespace Tests\Web\Pages;

use App\Models\Item;
use App\Models\ItemItemLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

class ItemItemLinkIndexTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    public function test_index_renders_request_driven_page_without_livewire_markup(): void
    {
        $item = Item::factory()->create(['internal_name' => 'Test Item']);
        $target = Item::factory()->create(['internal_name' => 'Target Item']);
        ItemItemLink::factory()->between($item, $target)->create();

        $response = $this->get(route('item-links.index', $item));

        $response
            ->assertOk()
            ->assertViewIs('item-links.index')
            ->assertSee('Target Item');

        $this->assertStringNotContainsString('livewire:dynamic-component', $response->getContent());
        $this->assertStringNotContainsString('wire:click', $response->getContent());
    }

    public function test_index_scopes_links_to_parent_item(): void
    {
        $item = Item::factory()->create(['internal_name' => 'Source Item']);
        $otherItem = Item::factory()->create(['internal_name' => 'Other Source']);
        $target = Item::factory()->create(['internal_name' => 'My Target']);
        $otherTarget = Item::factory()->create(['internal_name' => 'Other Target']);

        ItemItemLink::factory()->between($item, $target)->create();
        ItemItemLink::factory()->between($otherItem, $otherTarget)->create();

        $response = $this->get(route('item-links.index', $item));

        $response
            ->assertOk()
            ->assertSee('My Target')
            ->assertDontSee('Other Target');
    }

    public function test_index_returns_not_found_for_non_existent_item(): void
    {
        $response = $this->get(route('item-links.index', ['item' => 'non-existent-uuid']));

        $response->assertNotFound();
    }

    public function test_index_can_sort_by_target_internal_name(): void
    {
        $item = Item::factory()->create(['internal_name' => 'Source Item']);
        $targetA = Item::factory()->create(['internal_name' => 'Alpha Target']);
        $targetZ = Item::factory()->create(['internal_name' => 'Zeta Target']);

        ItemItemLink::factory()->between($item, $targetZ)->create();
        ItemItemLink::factory()->between($item, $targetA)->create();

        $response = $this->get(route('item-links.index', [
            'item' => $item,
            'sort' => 'target.internal_name',
            'direction' => 'asc',
        ]));

        $response
            ->assertOk()
            ->assertSeeInOrder(['Alpha Target', 'Zeta Target']);
    }

    public function test_index_rejects_invalid_sort_field_gracefully(): void
    {
        $item = Item::factory()->create();

        $response = $this->get(route('item-links.index', [
            'item' => $item,
            'sort' => 'invalid_field',
        ]));

        $response->assertOk();
    }

    public function test_index_requires_view_data_permission(): void
    {
        $item = Item::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('item-links.index', $item));

        $response->assertForbidden();
    }

    public function test_index_passes_parent_item_to_view(): void
    {
        $item = Item::factory()->create(['internal_name' => 'My Special Item']);

        $response = $this->get(route('item-links.index', $item));

        $response
            ->assertOk()
            ->assertSee('My Special Item');

        $this->assertSame($item->id, $response->viewData('item')->id);
    }

    public function test_index_preserves_query_strings_in_pagination_links(): void
    {
        $item = Item::factory()->create(['internal_name' => 'Paginated Item']);

        foreach (range(1, 11) as $index) {
            $target = Item::factory()->create(['internal_name' => 'Target '.str_pad((string) $index, 2, '0', STR_PAD_LEFT)]);
            ItemItemLink::factory()->between($item, $target)->create();
        }

        $response = $this->get(route('item-links.index', [
            'item' => $item,
            'per_page' => 10,
            'sort' => 'target.internal_name',
            'direction' => 'asc',
        ]));

        $response->assertOk();

        $paginator = $response->viewData('itemItemLinks');
        $nextPageUrl = $paginator->nextPageUrl();

        $this->assertNotNull($nextPageUrl);
        $this->assertStringContainsString('per_page=10', $nextPageUrl);
    }
}

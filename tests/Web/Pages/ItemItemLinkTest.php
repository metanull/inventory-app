<?php

namespace Tests\Web\Pages;

use App\Models\Context;
use App\Models\Item;
use App\Models\ItemItemLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\TestsWebNestedCrud;

class ItemItemLinkTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use TestsWebNestedCrud;

    protected function getParentModelClass()
    {
        return Item::class;
    }

    protected function getModelClass(): string
    {
        return ItemItemLink::class;
    }

    protected function getRouteName(): string
    {
        return 'item-links';
    }

    protected function getFormData(): array
    {
        return [
            'target_id' => Item::factory()->create()->id,
            'context_id' => Context::factory()->create()->id,
        ];
    }

    protected function getParentRouteParam()
    {
        return Item::factory()->create();
    }

    /**
     * Test that self-links are prevented.
     */
    public function test_self_links_are_prevented(): void
    {
        $item = Item::factory()->create();
        $context = Context::factory()->create();

        $data = [
            'target_id' => $item->id,
            'context_id' => $context->id,
        ];

        $response = $this->post(route('item-links.store', $item), $data);

        $response->assertSessionHasErrors('target_id');
        $this->assertDatabaseMissing('item_item_links', [
            'source_id' => $item->id,
            'target_id' => $item->id,
        ]);
    }

    /**
     * Test that duplicate links are prevented.
     */
    public function test_duplicate_links_are_prevented(): void
    {
        $item = Item::factory()->create();
        $targetItem = Item::factory()->create();
        $context = Context::factory()->create();

        // Create first link
        ItemItemLink::factory()
            ->between($item, $targetItem)
            ->inContext($context)
            ->create();

        // Try to create duplicate
        $data = [
            'target_id' => $targetItem->id,
            'context_id' => $context->id,
        ];

        $response = $this->post(route('item-links.store', $item), $data);

        $response->assertSessionHasErrors('context_id');
    }

    /**
     * Test that link from different item cannot be accessed.
     */
    public function test_link_from_different_item_returns_404(): void
    {
        $link = ItemItemLink::factory()->create();
        $otherItem = Item::factory()->create();

        $response = $this->get(route('item-links.show', [$otherItem, $link]));

        $response->assertNotFound();
    }

    /**
     * Test that link from different item cannot be updated.
     */
    public function test_link_from_different_item_cannot_be_updated(): void
    {
        $link = ItemItemLink::factory()->create();
        $otherItem = Item::factory()->create();
        $newTargetItem = Item::factory()->create();

        $data = [
            'target_id' => $newTargetItem->id,
            'context_id' => $link->context_id,
        ];

        $response = $this->put(route('item-links.update', [$otherItem, $link]), $data);

        $response->assertNotFound();
    }

    /**
     * Test that link from different item cannot be deleted.
     */
    public function test_link_from_different_item_cannot_be_deleted(): void
    {
        $link = ItemItemLink::factory()->create();
        $otherItem = Item::factory()->create();

        $response = $this->delete(route('item-links.destroy', [$otherItem, $link]));

        $response->assertNotFound();
    }
}

<?php

namespace Tests\Feature\Api\TagItem;

use App\Models\TagItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DestroyTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_delete_tag_item(): void
    {
        $tagItem = TagItem::factory()->create();

        $response = $this->deleteJson(route('tag-item.destroy', $tagItem));

        $response->assertNoContent();
        $this->assertDatabaseMissing('tag_items', [
            'id' => $tagItem->id,
        ]);
    }

    public function test_delete_returns_not_found_for_nonexistent_tag_item(): void
    {
        $response = $this->deleteJson(route('tag-item.destroy', 'nonexistent-uuid'));

        $response->assertNotFound();
    }

    public function test_can_delete_multiple_tag_items(): void
    {
        $tagItems = TagItem::factory()->count(3)->create();

        foreach ($tagItems as $tagItem) {
            $response = $this->deleteJson(route('tag-item.destroy', $tagItem));
            $response->assertNoContent();
            $this->assertDatabaseMissing('tag_items', [
                'id' => $tagItem->id,
            ]);
        }
    }

    public function test_delete_does_not_affect_other_tag_items(): void
    {
        $tagItem1 = TagItem::factory()->create();
        $tagItem2 = TagItem::factory()->create();

        $response = $this->deleteJson(route('tag-item.destroy', $tagItem1));

        $response->assertNoContent();
        $this->assertDatabaseMissing('tag_items', [
            'id' => $tagItem1->id,
        ]);
        $this->assertDatabaseHas('tag_items', [
            'id' => $tagItem2->id,
        ]);
    }

    public function test_delete_does_not_affect_related_tag_and_item(): void
    {
        $tagItem = TagItem::factory()->create();
        $tagId = $tagItem->tag_id;
        $itemId = $tagItem->item_id;

        $response = $this->deleteJson(route('tag-item.destroy', $tagItem));

        $response->assertNoContent();
        $this->assertDatabaseMissing('tag_items', [
            'id' => $tagItem->id,
        ]);
        // Ensure the related tag and item still exist
        $this->assertDatabaseHas('tags', [
            'id' => $tagId,
        ]);
        $this->assertDatabaseHas('items', [
            'id' => $itemId,
        ]);
    }
}

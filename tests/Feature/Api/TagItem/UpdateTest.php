<?php

namespace Tests\Feature\Api\TagItem;

use App\Models\Item;
use App\Models\Tag;
use App\Models\TagItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateTest extends TestCase
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

    public function test_can_update_tag_item(): void
    {
        $tagItem = TagItem::factory()->create();
        $newTag = Tag::factory()->create();
        $newItem = Item::factory()->create();
        $updateData = [
            'tag_id' => $newTag->id,
            'item_id' => $newItem->id,
        ];

        $response = $this->putJson(route('tag-item.update', $tagItem), $updateData);

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'tag_id',
                'item_id',
                'tag',
                'item',
                'created_at',
                'updated_at',
            ],
        ]);
        $this->assertDatabaseHas('tag_items', [
            'id' => $tagItem->id,
            'tag_id' => $newTag->id,
            'item_id' => $newItem->id,
        ]);
    }

    public function test_update_returns_updated_tag_item_with_relationships(): void
    {
        $tagItem = TagItem::factory()->create();
        $newTag = Tag::factory()->create();
        $newItem = Item::factory()->create();
        $updateData = [
            'tag_id' => $newTag->id,
            'item_id' => $newItem->id,
        ];

        $response = $this->putJson(route('tag-item.update', $tagItem), $updateData);

        $response->assertOk();
        $response->assertJsonPath('data.id', $tagItem->id);
        $response->assertJsonPath('data.tag_id', $newTag->id);
        $response->assertJsonPath('data.item_id', $newItem->id);
        $response->assertJsonPath('data.tag.id', $newTag->id);
        $response->assertJsonPath('data.item.id', $newItem->id);
    }

    public function test_cannot_update_tag_item_without_tag_id(): void
    {
        $tagItem = TagItem::factory()->create();
        $newItem = Item::factory()->create();
        $updateData = [
            'item_id' => $newItem->id,
        ];

        $response = $this->putJson(route('tag-item.update', $tagItem), $updateData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['tag_id']);
    }

    public function test_cannot_update_tag_item_without_item_id(): void
    {
        $tagItem = TagItem::factory()->create();
        $newTag = Tag::factory()->create();
        $updateData = [
            'tag_id' => $newTag->id,
        ];

        $response = $this->putJson(route('tag-item.update', $tagItem), $updateData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['item_id']);
    }

    public function test_cannot_update_tag_item_with_invalid_tag_id(): void
    {
        $tagItem = TagItem::factory()->create();
        $newItem = Item::factory()->create();
        $updateData = [
            'tag_id' => 'invalid-uuid',
            'item_id' => $newItem->id,
        ];

        $response = $this->putJson(route('tag-item.update', $tagItem), $updateData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['tag_id']);
    }

    public function test_cannot_update_tag_item_with_invalid_item_id(): void
    {
        $tagItem = TagItem::factory()->create();
        $newTag = Tag::factory()->create();
        $updateData = [
            'tag_id' => $newTag->id,
            'item_id' => 'invalid-uuid',
        ];

        $response = $this->putJson(route('tag-item.update', $tagItem), $updateData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['item_id']);
    }

    public function test_cannot_update_tag_item_with_nonexistent_tag(): void
    {
        $tagItem = TagItem::factory()->create();
        $newItem = Item::factory()->create();
        $updateData = [
            'tag_id' => $this->faker->uuid(),
            'item_id' => $newItem->id,
        ];

        $response = $this->putJson(route('tag-item.update', $tagItem), $updateData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['tag_id']);
    }

    public function test_cannot_update_tag_item_with_nonexistent_item(): void
    {
        $tagItem = TagItem::factory()->create();
        $newTag = Tag::factory()->create();
        $updateData = [
            'tag_id' => $newTag->id,
            'item_id' => $this->faker->uuid(),
        ];

        $response = $this->putJson(route('tag-item.update', $tagItem), $updateData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['item_id']);
    }

    public function test_cannot_update_tag_item_with_id_field(): void
    {
        $tagItem = TagItem::factory()->create();
        $newTag = Tag::factory()->create();
        $newItem = Item::factory()->create();
        $updateData = [
            'id' => $this->faker->uuid(),
            'tag_id' => $newTag->id,
            'item_id' => $newItem->id,
        ];

        $response = $this->putJson(route('tag-item.update', $tagItem), $updateData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_update_returns_not_found_for_nonexistent_tag_item(): void
    {
        $newTag = Tag::factory()->create();
        $newItem = Item::factory()->create();
        $updateData = [
            'tag_id' => $newTag->id,
            'item_id' => $newItem->id,
        ];

        $response = $this->putJson(route('tag-item.update', 'nonexistent-uuid'), $updateData);

        $response->assertNotFound();
    }
}

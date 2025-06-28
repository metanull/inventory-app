<?php

namespace Tests\Feature\Api\TagItem;

use App\Models\Item;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StoreTest extends TestCase
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

    public function test_can_create_tag_item(): void
    {
        $tag = Tag::factory()->create();
        $item = Item::factory()->create();
        $tagItemData = [
            'tag_id' => $tag->id,
            'item_id' => $item->id,
        ];

        $response = $this->postJson(route('tag-item.store'), $tagItemData);

        $response->assertCreated();
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
        $this->assertDatabaseHas('tag_items', $tagItemData);
    }

    public function test_store_returns_created_tag_item_with_relationships(): void
    {
        $tag = Tag::factory()->create();
        $item = Item::factory()->create();
        $tagItemData = [
            'tag_id' => $tag->id,
            'item_id' => $item->id,
        ];

        $response = $this->postJson(route('tag-item.store'), $tagItemData);

        $response->assertCreated();
        $response->assertJsonPath('data.tag_id', $tag->id);
        $response->assertJsonPath('data.item_id', $item->id);
        $response->assertJsonPath('data.tag.id', $tag->id);
        $response->assertJsonPath('data.item.id', $item->id);
    }

    public function test_cannot_create_tag_item_without_tag_id(): void
    {
        $item = Item::factory()->create();
        $tagItemData = [
            'item_id' => $item->id,
        ];

        $response = $this->postJson(route('tag-item.store'), $tagItemData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['tag_id']);
    }

    public function test_cannot_create_tag_item_without_item_id(): void
    {
        $tag = Tag::factory()->create();
        $tagItemData = [
            'tag_id' => $tag->id,
        ];

        $response = $this->postJson(route('tag-item.store'), $tagItemData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['item_id']);
    }

    public function test_cannot_create_tag_item_with_invalid_tag_id(): void
    {
        $item = Item::factory()->create();
        $tagItemData = [
            'tag_id' => 'invalid-uuid',
            'item_id' => $item->id,
        ];

        $response = $this->postJson(route('tag-item.store'), $tagItemData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['tag_id']);
    }

    public function test_cannot_create_tag_item_with_invalid_item_id(): void
    {
        $tag = Tag::factory()->create();
        $tagItemData = [
            'tag_id' => $tag->id,
            'item_id' => 'invalid-uuid',
        ];

        $response = $this->postJson(route('tag-item.store'), $tagItemData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['item_id']);
    }

    public function test_cannot_create_tag_item_with_nonexistent_tag(): void
    {
        $item = Item::factory()->create();
        $tagItemData = [
            'tag_id' => $this->faker->uuid(),
            'item_id' => $item->id,
        ];

        $response = $this->postJson(route('tag-item.store'), $tagItemData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['tag_id']);
    }

    public function test_cannot_create_tag_item_with_nonexistent_item(): void
    {
        $tag = Tag::factory()->create();
        $tagItemData = [
            'tag_id' => $tag->id,
            'item_id' => $this->faker->uuid(),
        ];

        $response = $this->postJson(route('tag-item.store'), $tagItemData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['item_id']);
    }

    public function test_cannot_create_tag_item_with_id_field(): void
    {
        $tag = Tag::factory()->create();
        $item = Item::factory()->create();
        $tagItemData = [
            'id' => $this->faker->uuid(),
            'tag_id' => $tag->id,
            'item_id' => $item->id,
        ];

        $response = $this->postJson(route('tag-item.store'), $tagItemData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }
}

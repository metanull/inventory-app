<?php

namespace Tests\Feature\Api\TagItem;

use App\Models\TagItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowTest extends TestCase
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

    public function test_can_show_tag_item(): void
    {
        $tagItem = TagItem::factory()->create();

        $response = $this->getJson(route('tag-item.show', $tagItem));

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
    }

    public function test_show_returns_correct_tag_item_data(): void
    {
        $tagItem = TagItem::factory()->create();

        $response = $this->getJson(route('tag-item.show', $tagItem));

        $response->assertOk();
        $response->assertJsonPath('data.id', $tagItem->id);
        $response->assertJsonPath('data.tag_id', $tagItem->tag_id);
        $response->assertJsonPath('data.item_id', $tagItem->item_id);
    }

    public function test_show_includes_tag_and_item_relationships(): void
    {
        $tagItem = TagItem::factory()->create();

        $response = $this->getJson(route('tag-item.show', $tagItem));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'tag' => [
                    'id',
                    'internal_name',
                    'description',
                ],
                'item' => [
                    'id',
                    'internal_name',
                    'type',
                ],
            ],
        ]);
    }

    public function test_show_returns_not_found_for_nonexistent_tag_item(): void
    {
        $response = $this->getJson(route('tag-item.show', 'nonexistent-uuid'));

        $response->assertNotFound();
    }

    public function test_show_returns_tag_item_with_correct_relationships(): void
    {
        $tagItem = TagItem::factory()->create();
        $tagItem->load(['tag', 'item']);

        $response = $this->getJson(route('tag-item.show', $tagItem));

        $response->assertOk();
        $response->assertJsonPath('data.tag.id', $tagItem->tag->id);
        $response->assertJsonPath('data.item.id', $tagItem->item->id);
    }
}

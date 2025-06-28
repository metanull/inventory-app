<?php

namespace Tests\Feature\Api\TagItem;

use App\Models\TagItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IndexTest extends TestCase
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

    public function test_can_list_tag_items(): void
    {
        $tagItems = TagItem::factory()->count(3)->create();

        $response = $this->getJson(route('tag-item.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'tag_id',
                    'item_id',
                    'tag',
                    'item',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
        $response->assertJsonCount(3, 'data');
    }

    public function test_can_list_empty_tag_items(): void
    {
        $response = $this->getJson(route('tag-item.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [],
        ]);
        $response->assertJsonCount(0, 'data');
    }

    public function test_index_includes_tag_and_item_relationships(): void
    {
        $tagItem = TagItem::factory()->create();

        $response = $this->getJson(route('tag-item.index'));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'tag_id',
                    'item_id',
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
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    public function test_index_returns_correct_tag_item_data(): void
    {
        $tagItem = TagItem::factory()->create();

        $response = $this->getJson(route('tag-item.index'));

        $response->assertOk();
        $response->assertJsonPath('data.0.id', $tagItem->id);
        $response->assertJsonPath('data.0.tag_id', $tagItem->tag_id);
        $response->assertJsonPath('data.0.item_id', $tagItem->item_id);
    }
}

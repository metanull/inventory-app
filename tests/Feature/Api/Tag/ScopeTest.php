<?php

namespace Tests\Feature\Api\Tag;

use App\Models\Item;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ScopeTest extends TestCase
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

    public function test_can_get_tags_for_item(): void
    {
        $item = Item::factory()->create();
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $tag3 = Tag::factory()->create();

        $item->tags()->attach([$tag1->id, $tag2->id]);

        $response = $this->getJson(route('tag.forItem', $item));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'internal_name',
                    'description',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
        $response->assertJsonCount(2, 'data');

        $tagIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($tag1->id, $tagIds);
        $this->assertContains($tag2->id, $tagIds);
        $this->assertNotContains($tag3->id, $tagIds);
    }

    public function test_returns_empty_when_item_has_no_tags(): void
    {
        $item = Item::factory()->create();

        $response = $this->getJson(route('tag.forItem', $item));

        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_returns_not_found_for_nonexistent_item(): void
    {
        $response = $this->getJson(route('tag.forItem', 'nonexistent-uuid'));

        $response->assertNotFound();
    }
}

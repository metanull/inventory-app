<?php

namespace Tests\Feature\Api\Item;

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

    public function test_can_get_items_for_tag(): void
    {
        $tag = Tag::factory()->create();
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();
        $item3 = Item::factory()->create();

        $tag->items()->attach([$item1->id, $item2->id]);

        $response = $this->getJson(route('item.forTag', $tag));

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'internal_name',
                    'backward_compatibility',
                    'type',
                    'owner_reference',
                    'mwnf_reference',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
        $response->assertJsonCount(2, 'data');

        $itemIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($item1->id, $itemIds);
        $this->assertContains($item2->id, $itemIds);
        $this->assertNotContains($item3->id, $itemIds);
    }

    public function test_can_get_items_with_all_tags(): void
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $tag3 = Tag::factory()->create();
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();
        $item3 = Item::factory()->create();

        // Item1 has tag1 and tag2
        $item1->tags()->attach([$tag1->id, $tag2->id]);

        // Item2 has only tag1
        $item2->tags()->attach($tag1->id);

        // Item3 has tag2 and tag3
        $item3->tags()->attach([$tag2->id, $tag3->id]);

        $response = $this->postJson(route('item.withAllTags'), [
            'tags' => [$tag1->id, $tag2->id],
        ]);

        $response->assertOk();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.id', $item1->id);
    }

    public function test_can_get_items_with_any_tags(): void
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $tag3 = Tag::factory()->create();
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();
        $item3 = Item::factory()->create();

        // Item1 has tag1
        $item1->tags()->attach($tag1->id);

        // Item2 has tag2
        $item2->tags()->attach($tag2->id);

        // Item3 has tag3 (not in our search)
        $item3->tags()->attach($tag3->id);

        $response = $this->postJson(route('item.withAnyTags'), [
            'tags' => [$tag1->id, $tag2->id],
        ]);

        $response->assertOk();
        $response->assertJsonCount(2, 'data');

        $itemIds = collect($response->json('data'))->pluck('id')->toArray();
        $this->assertContains($item1->id, $itemIds);
        $this->assertContains($item2->id, $itemIds);
        $this->assertNotContains($item3->id, $itemIds);
    }

    public function test_with_all_tags_requires_tags_array(): void
    {
        $response = $this->postJson(route('item.withAllTags'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['tags']);
    }

    public function test_with_all_tags_requires_valid_tag_ids(): void
    {
        $response = $this->postJson(route('item.withAllTags'), [
            'tags' => ['invalid-uuid'],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['tags.0']);
    }

    public function test_with_all_tags_requires_existing_tags(): void
    {
        $response = $this->postJson(route('item.withAllTags'), [
            'tags' => [$this->faker->uuid()],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['tags.0']);
    }

    public function test_with_any_tags_requires_tags_array(): void
    {
        $response = $this->postJson(route('item.withAnyTags'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['tags']);
    }

    public function test_with_any_tags_requires_valid_tag_ids(): void
    {
        $response = $this->postJson(route('item.withAnyTags'), [
            'tags' => ['invalid-uuid'],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['tags.0']);
    }

    public function test_with_any_tags_requires_existing_tags(): void
    {
        $response = $this->postJson(route('item.withAnyTags'), [
            'tags' => [$this->faker->uuid()],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['tags.0']);
    }

    public function test_returns_empty_when_tag_has_no_items(): void
    {
        $tag = Tag::factory()->create();

        $response = $this->getJson(route('item.forTag', $tag));

        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_returns_not_found_for_nonexistent_tag(): void
    {
        $response = $this->getJson(route('item.forTag', 'nonexistent-uuid'));

        $response->assertNotFound();
    }

    public function test_with_all_tags_returns_empty_when_no_items_match(): void
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $response = $this->postJson(route('item.withAllTags'), [
            'tags' => [$tag1->id, $tag2->id],
        ]);

        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    public function test_with_any_tags_returns_empty_when_no_items_match(): void
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $response = $this->postJson(route('item.withAnyTags'), [
            'tags' => [$tag1->id, $tag2->id],
        ]);

        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }
}

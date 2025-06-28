<?php

namespace Tests\Feature\Api\TagItem;

use App\Models\TagItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        // No authentication for anonymous tests
    }

    public function test_anonymous_cannot_access_tag_item_index(): void
    {
        $response = $this->getJson(route('tag-item.index'));

        $response->assertUnauthorized();
    }

    public function test_anonymous_cannot_access_tag_item_show(): void
    {
        $tagItem = TagItem::factory()->create();

        $response = $this->getJson(route('tag-item.show', $tagItem));

        $response->assertUnauthorized();
    }

    public function test_anonymous_cannot_create_tag_item(): void
    {
        $tagItemData = TagItem::factory()->make()->toArray();

        $response = $this->postJson(route('tag-item.store'), $tagItemData);

        $response->assertUnauthorized();
    }

    public function test_anonymous_cannot_update_tag_item(): void
    {
        $tagItem = TagItem::factory()->create();
        $updateData = TagItem::factory()->make()->toArray();

        $response = $this->putJson(route('tag-item.update', $tagItem), $updateData);

        $response->assertUnauthorized();
    }

    public function test_anonymous_cannot_delete_tag_item(): void
    {
        $tagItem = TagItem::factory()->create();

        $response = $this->deleteJson(route('tag-item.destroy', $tagItem));

        $response->assertUnauthorized();
    }
}

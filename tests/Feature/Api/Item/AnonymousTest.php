<?php

namespace Tests\Feature\Api\Item;

use App\Models\Item;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_index_forbids_anonymous_access(): void
    {
        $response = $this->getJson(route('item.index'));
        $response->assertUnauthorized();
    }

    public function test_show_forbids_anonymous_access(): void
    {
        $item = Item::factory()->create();
        $response = $this->getJson(route('item.show', $item->id));
        $response->assertUnauthorized();
    }

    public function test_store_forbids_anonymous_access(): void
    {
        $response = $this->postJson(route('item.store'), [
            'partner_id' => Partner::factory()->create()->id,
            'internal_name' => 'Test Item',
            'backward_compatibility' => 'TI',
            'type' => 'object',
        ]);
        $response->assertUnauthorized();
    }

    public function test_update_forbids_anonymous_access(): void
    {
        $item = Item::factory()->create();
        $response = $this->putJson(route('item.update', $item->id), [
            'partner_id' => Partner::factory()->create()->id,
            'internal_name' => 'Updated Item',
            'backward_compatibility' => 'UI',
            'type' => 'monument',
        ]);
        $response->assertUnauthorized();
    }

    public function test_destroy_forbids_anonymous_access(): void
    {
        $item = Item::factory()->create();
        $response = $this->deleteJson(route('item.destroy', $item->id));
        $response->assertUnauthorized();
    }

    public function test_update_tags_forbids_anonymous_access(): void
    {
        $item = Item::factory()->create();
        $response = $this->patchJson(route('item.updateTags', $item->id), [
            'attach' => [],
            'detach' => [],
        ]);
        $response->assertUnauthorized();
    }

    public function test_attach_tag_forbids_anonymous_access(): void
    {
        $item = Item::factory()->create();
        $response = $this->postJson(route('item.attachTag', $item->id), [
            'tag_id' => 'test-uuid',
        ]);
        $response->assertUnauthorized();
    }

    public function test_detach_tag_forbids_anonymous_access(): void
    {
        $item = Item::factory()->create();
        $response = $this->deleteJson(route('item.detachTag', $item->id), [
            'tag_id' => 'test-uuid',
        ]);
        $response->assertUnauthorized();
    }

    public function test_attach_tags_forbids_anonymous_access(): void
    {
        $item = Item::factory()->create();
        $response = $this->postJson(route('item.attachTags', $item->id), [
            'tag_ids' => [],
        ]);
        $response->assertUnauthorized();
    }

    public function test_detach_tags_forbids_anonymous_access(): void
    {
        $item = Item::factory()->create();
        $response = $this->deleteJson(route('item.detachTags', $item->id), [
            'tag_ids' => [],
        ]);
        $response->assertUnauthorized();
    }
}

<?php

namespace Tests\Feature\Api\Detail;

use App\Models\Detail;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_index_forbids_anonymous_access(): void
    {
        $response = $this->getJson(route('detail.index'));
        $response->assertUnauthorized();
    }

    public function test_show_forbids_anonymous_access(): void
    {
        $detail = Detail::factory()->for(Item::factory())->create();
        $response = $this->getJson(route('detail.show', $detail->id));
        $response->assertUnauthorized();
    }

    public function test_store_forbids_anonymous_access(): void
    {
        $response = $this->postJson(route('detail.store'), [
            'item_id' => Item::Factory()->create()->id,
            'internal_name' => 'Test Detail',
            'backward_compatibility' => 'TD',
        ]);
        $response->assertUnauthorized();
    }

    public function test_update_forbids_anonymous_access(): void
    {
        $detail = Detail::factory()->for(Item::factory())->create();
        $response = $this->putJson(route('detail.update', $detail->id), [
            'item_id' => Item::Factory()->create()->id,
            'internal_name' => 'Updated Detail',
            'backward_compatibility' => 'UD',
        ]);
        $response->assertUnauthorized();
    }

    public function test_destroy_forbids_anonymous_access(): void
    {
        $detail = Detail::factory()->for(Item::factory())->create();
        $response = $this->deleteJson(route('detail.destroy', $detail->id));
        $response->assertUnauthorized();
    }
}

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
        $response = $this->withHeaders(['Authorization' => ''])
            ->getJson(route('item.index'));
        $response->assertUnauthorized();
    }

    public function test_show_forbids_anonymous_access(): void
    {
        $item = Item::factory()->create();
        $response = $this->withHeaders(['Authorization' => ''])
            ->getJson(route('item.show', $item->id));
        $response->assertUnauthorized();
    }

    public function test_store_forbids_anonymous_access(): void
    {
        $response = $this->withHeaders(['Authorization' => ''])
            ->postJson(route('item.store'), [
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
        $response = $this->withHeaders(['Authorization' => ''])
            ->putJson(route('item.update', $item->id), [
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
        $response = $this->withHeaders(['Authorization' => ''])
            ->deleteJson(route('item.destroy', $item->id));
        $response->assertUnauthorized();
    }
}

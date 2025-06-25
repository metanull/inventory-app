<?php

namespace Tests\Feature\Api\Detail;

use App\Models\Detail;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_show_allows_authenticated_users(): void
    {
        $detail = Detail::factory()->for(Item::factory())->create();
        $response_authenticated = $this->getJson(route('detail.show', $detail->id));
        $response_authenticated->assertOk();
    }

    public function test_show_returns_not_found_when_not_found(): void
    {
        $response = $this->getJson(route('detail.show', 'nonexistent'));

        $response->assertNotFound();
    }

    public function test_show_returns_the_expected_structure(): void
    {
        $detail = Detail::factory()->for(Item::factory())->create();
        $response = $this->getJson(route('detail.show', $detail->id));

        $response->assertJsonStructure([
            'data' => [
                'id',
                'item',
                'internal_name',
                'backward_compatibility',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_show_returns_the_expected_structure_including_item_data(): void
    {
        $detail = Detail::factory()->for(Item::factory())->create();
        $response = $this->getJson(route('detail.show', $detail->id));

        $response->assertJsonStructure([
            'data' => [
                'item' => [
                    'id',
                    'internal_name',
                    'backward_compatibility',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    public function test_show_returns_the_expected_data(): void
    {
        $detail = Detail::factory()->for(Item::factory())->create();
        $response = $this->getJson(route('detail.show', $detail->id));

        $response->assertJsonPath('data.id', $detail->id)
            ->assertJsonPath('data.internal_name', $detail->internal_name)
            ->assertJsonPath('data.backward_compatibility', $detail->backward_compatibility);
    }

    public function test_show_returns_the_expected_data_including_item_data(): void
    {
        $detail = Detail::factory()->for(Item::factory())->create();
        $response = $this->getJson(route('detail.show', $detail->id));

        $response->assertJsonPath('data.id', $detail->id)
            ->assertJsonPath('data.item.id', $detail->item->id)
            ->assertJsonPath('data.item.internal_name', $detail->item->internal_name)
            ->assertJsonPath('data.item.backward_compatibility', $detail->item->backward_compatibility);
    }
}

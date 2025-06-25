<?php

namespace Tests\Feature\Api\Detail;

use App\Models\Detail;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_api_authentication_index_allows_authenticated_users(): void
    {
        $response_authenticated = $this->getJson(route('detail.index'));
        $response_authenticated->assertOk();
    }

    public function test_api_response_index_returns_ok_when_no_data(): void
    {
        $response = $this->getJson(route('detail.index'));

        $response->assertOk();
    }

    public function test_api_response_index_returns_an_empty_array_when_no_data(): void
    {
        $response = $this->getJson(route('detail.index'));

        $response->assertJsonCount(0, 'data');
    }

    public function test_api_response_index_returns_the_expected_structure(): void
    {
        $detail = Detail::factory()->for(Item::factory())->create();
        $response = $this->getJson(route('detail.index'));

        $response->assertExactJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'item',
                    'internal_name',
                    'backward_compatibility',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    public function test_api_response_index_returns_the_expected_structure_including_item_data(): void
    {
        $detail = Detail::factory()->for(Item::factory())->create();
        $response = $this->getJson(route('detail.index'));

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'item' => [
                        'id',
                        'internal_name',
                        'backward_compatibility',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ],
        ]);
    }

    public function test_api_response_index_returns_the_expected_data(): void
    {
        $detail1 = Detail::factory()->WithItem()->create();
        $detail2 = Detail::factory()->WithItem()->create();
        $response = $this->getJson(route('detail.index'));

        $response->assertJsonPath('data.0.id', $detail1->id)
            ->assertJsonPath('data.0.internal_name', $detail1->internal_name)
            ->assertJsonPath('data.0.backward_compatibility', $detail1->backward_compatibility)
            ->assertJsonPath('data.1.id', $detail2->id)
            ->assertJsonPath('data.1.internal_name', $detail2->internal_name)
            ->assertJsonPath('data.1.backward_compatibility', $detail2->backward_compatibility);
    }
}

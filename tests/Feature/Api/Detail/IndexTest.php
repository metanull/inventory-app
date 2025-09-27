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

    public function test_index_allows_authenticated_users(): void
    {
        $response_authenticated = $this->getJson(route('detail.index'));
        $response_authenticated->assertOk();
    }

    public function test_index_returns_ok_when_no_data(): void
    {
        $response = $this->getJson(route('detail.index'));

        $response->assertOk();
    }

    public function test_index_returns_an_empty_array_when_no_data(): void
    {
        $response = $this->getJson(route('detail.index'));
        $response->assertJsonPath('meta.total', 0);
    }

    public function test_index_returns_the_expected_structure(): void
    {
        Detail::factory()->for(Item::factory())->create();
        $response = $this->getJson(route('detail.index'));

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'internal_name',
                    'backward_compatibility',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links' => [
                'first', 'last', 'prev', 'next',
            ],
            'meta' => [
                'current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total',
            ],
        ]);
    }

    public function test_index_returns_the_expected_structure_including_item_data(): void
    {
        Detail::factory()->for(Item::factory())->create();
        $response = $this->getJson(route('detail.index', ['include' => 'item']));

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

    public function test_index_returns_the_expected_data(): void
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

    public function test_index_accepts_pagination_parameters(): void
    {
        Detail::factory(5)->WithItem()->create();

        $response = $this->getJson(route('detail.index', ['per_page' => 2, 'page' => 1]));

        $response->assertOk()
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.current_page', 1);

        $this->assertCount(2, $response->json('data'));
    }

    public function test_index_validates_pagination_parameters(): void
    {
        $response = $this->getJson(route('detail.index', ['per_page' => 0]));
        $response->assertUnprocessable();

        $response = $this->getJson(route('detail.index', ['per_page' => 101]));
        $response->assertUnprocessable();

        $response = $this->getJson(route('detail.index', ['page' => 0]));
        $response->assertUnprocessable();
    }
}

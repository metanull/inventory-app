<?php

namespace Tests\Feature\Api\Item;

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
        $response = $this->getJson(route('item.index'));
        $response->assertOk();
    }

    public function test_index_returns_ok_when_no_data(): void
    {
        $response = $this->getJson(route('item.index'));
        $response->assertOk();
    }

    public function test_index_returns_an_empty_array_when_no_data(): void
    {
        $response = $this->getJson(route('item.index'));
        $response->assertJsonCount(0, 'data');
    }

    public function test_index_returns_the_expected_structure(): void
    {
        $item = Item::factory()->create();
        $response = $this->getJson(route('item.index'));

        $response->assertExactJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'partner',
                    'internal_name',
                    'backward_compatibility',
                    'type',
                    'owner_reference',
                    'mwnf_reference',
                    'country',
                    'project',
                    'artists',
                    'workshops',
                    'tags',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    public function test_index_returns_the_expected_structure_including_partner_data(): void
    {
        $item = Item::factory()->WithPartner()->create();
        $response = $this->getJson(route('item.index'));

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'partner' => [
                        'id',
                        'internal_name',
                        'backward_compatibility',
                        'country',
                        'type',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ],
        ]);
    }

    public function test_index_returns_the_expected_structure_including_country_data(): void
    {
        $item = Item::factory()->WithCountry()->create();
        $response = $this->getJson(route('item.index'));

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'country' => [
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

    public function test_index_returns_the_expected_structure_including_project_data(): void
    {
        $item = Item::factory()->WithProject()->create();
        $response = $this->getJson(route('item.index'));

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'project' => [
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
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();
        $response = $this->getJson(route('item.index'));

        $response->assertJsonPath('data.0.id', $item1->id)
            ->assertJsonPath('data.0.internal_name', $item1->internal_name)
            ->assertJsonPath('data.0.backward_compatibility', $item1->backward_compatibility)
            ->assertJsonPath('data.0.type', $item1->type)
            ->assertJsonPath('data.1.id', $item2->id)
            ->assertJsonPath('data.1.internal_name', $item2->internal_name)
            ->assertJsonPath('data.1.backward_compatibility', $item2->backward_compatibility)
            ->assertJsonPath('data.1.type', $item2->type);
    }
}

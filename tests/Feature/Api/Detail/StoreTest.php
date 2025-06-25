<?php

namespace Tests\Feature\Api\Detail;

use App\Models\Detail;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_store_allows_authenticated_users(): void
    {
        $response_authenticated = $this->postJson(route('detail.store'), [
                'item_id' => Item::Factory()->create()->id,
                'internal_name' => 'Test Detail',
                'backward_compatibility' => 'TD',
            ]);
        $response_authenticated->assertCreated();
    }

    public function test_store_validates_its_input(): void
    {
        $response = $this->postJson(route('detail.store'), [
                'id' => 'invalid-id', // Invalid: prohibited field
                'item_id' => 'invalid_id', // Invalid: not a valid Item ID
                'internal_name' => '', // Invalid: required field
                'backward_compatibility' => null,
            ]);

        $response->assertJsonValidationErrors(['id', 'internal_name', 'item_id']);
    }

    public function test_store_returns_unprocessable_when_input_is_invalid(): void
    {
        $response = $this->postJson(route('detail.store'), [
                'id' => 'invalid-id', // Invalid: prohibited field
                'item_id' => 'invalid_id', // Invalid: not a valid Item ID
                'internal_name' => '', // Invalid: required field
                'backward_compatibility' => 'TD',
            ]);

        $response->assertUnprocessable();
    }

    public function test_store_inserts_a_row(): void
    {
        $item = Item::factory()->create();

        $response = $this->postJson(route('detail.store'), [
                'item_id' => $item->id,
                'internal_name' => 'Test Detail',
                'backward_compatibility' => 'TD',
            ]);

        $this->assertDatabaseHas('details', [
            'internal_name' => 'Test Detail',
            'backward_compatibility' => 'TD',
            'item_id' => $item->id,
        ]);
    }

    public function test_store_returns_created_on_success(): void
    {
        $item = Item::factory()->create();

        $response = $this->postJson(route('detail.store'), [
                'item_id' => $item->id,
                'internal_name' => 'Test Detail',
                'backward_compatibility' => 'TD',
            ]);

        $response->assertCreated();
    }

    public function test_store_returns_the_expected_structure(): void
    {
        $item = Item::factory()->create();

        $response = $this->postJson(route('detail.store'), [
                'item_id' => $item->id,
                'internal_name' => 'Test Detail',
                'backward_compatibility' => 'TD',
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
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

    public function test_store_returns_the_expected_structure_including_item_data(): void
    {
        $item = Item::factory()->create();

        $response = $this->postJson(route('detail.store'), [
                'item_id' => $item->id,
                'internal_name' => 'Test Detail',
                'backward_compatibility' => 'TD',
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
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

    public function test_store_returns_the_expected_data(): void
    {
        $response = $this->postJson(route('detail.store'), [
                'item_id' => Item::Factory()->create()->id,
                'internal_name' => 'Test Detail',
                'backward_compatibility' => 'TD',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.internal_name', 'Test Detail')
            ->assertJsonPath('data.backward_compatibility', 'TD');
    }

    public function test_store_returns_the_expected_data_including_item_data(): void
    {
        $item = Item::factory()->create();

        $response = $this->postJson(route('detail.store'), [
                'item_id' => $item->id,
                'internal_name' => 'Test Detail',
                'backward_compatibility' => 'TD',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.item.id', $item->id)
            ->assertJsonPath('data.item.internal_name', $item->internal_name)
            ->assertJsonPath('data.item.backward_compatibility', $item->backward_compatibility);
    }
}

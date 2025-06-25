<?php

namespace Tests\Feature\Api\Detail;

use App\Models\Detail;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_update_allows_authenticated_users(): void
    {
        $detail = Detail::factory()->for(Item::factory())->create();
        $response_authenticated = $this->putJson(route('detail.update', $detail->id), [
                'item_id' => Item::Factory()->create()->id,
                'internal_name' => 'Updated Detail',
                'backward_compatibility' => 'UD',
            ]);
        $response_authenticated->assertOk();
    }

    public function test_update_validates_its_input(): void
    {
        $detail = Detail::factory()->for(Item::factory())->create();

        $response = $this->putJson(route('detail.update', $detail->id), [
                'id' => 'invalid-id', // Invalid: prohibited field
                'item_id' => 'invalid_id', // Invalid: not a valid Item ID
                'internal_name' => '', // Invalid: required field
                'backward_compatibility' => 'UD',
            ]);

        $response->assertJsonValidationErrors(['id', 'internal_name', 'item_id']);
    }

    public function test_update_returns_unprocessable_when_input_is_invalid(): void
    {
        $detail = Detail::factory()->for(Item::factory())->create();

        $response = $this->putJson(route('detail.update', $detail->id), [
                'id' => 'invalid-id', // Invalid: prohibited field
                'item_id' => 'invalid_id', // Invalid: not a valid Item ID
                'internal_name' => '', // Invalid: required field
                'backward_compatibility' => 'UD',
            ]);

        $response->assertUnprocessable();
    }

    public function test_update_returns_not_found_response_when_not_found(): void
    {
        $response = $this->putJson(route('detail.update', 'nonexistent'), [
                'item_id' => Item::Factory()->create()->id,
                'internal_name' => 'Updated Detail',
                'backward_compatibility' => 'UD123',
            ]);

        $response->assertNotFound();
    }

    public function test_update_updates_a_row(): void
    {
        $detail = Detail::factory()->for(Item::factory())->create();
        $other_item = Item::factory()->create();

        $this->putJson(route('detail.update', $detail->id), [
                'item_id' => $other_item->id,
                'internal_name' => 'Updated Detail',
                'backward_compatibility' => 'UD123',
            ]);

        $this->assertDatabaseHas('details', [
            'id' => $detail->id,
            'item_id' => $other_item->id,
            'internal_name' => 'Updated Detail',
            'backward_compatibility' => 'UD123',
        ]);
    }

    public function test_update_returns_ok_on_success(): void
    {
        $detail = Detail::factory()->for(Item::factory())->create();

        $response = $this->putJson(route('detail.update', $detail->id), [
                'item_id' => $detail->item_id,
                'internal_name' => 'Updated Detail',
                'backward_compatibility' => 'UD123',
            ]);

        $response->assertOk();
    }

    public function test_update_returns_the_expected_structure(): void
    {
        $detail = Detail::factory()->for(Item::factory())->create();

        $response = $this->putJson(route('detail.update', $detail->id), [
                'item_id' => $detail->item_id,
                'internal_name' => 'Updated Detail',
                'backward_compatibility' => 'UD123',
            ]);

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

    public function test_update_returns_the_expected_structure_including_item_data(): void
    {
        $detail = Detail::factory()->for(Item::factory())->create();
        $item = Item::factory()->create();

        $response = $this->putJson(route('detail.update', $detail->id), [
                'item_id' => $item->id,
                'internal_name' => 'Updated Detail',
                'backward_compatibility' => 'UD123',
            ]);

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

    public function test_update_returns_the_expected_data(): void
    {
        $detail = Detail::factory()->for(Item::factory())->create();

        $response = $this->putJson(route('detail.update', $detail->id), [
                'item_id' => $detail->item_id,
                'internal_name' => 'Updated Detail',
                'backward_compatibility' => 'UD123',
            ]);

        $response->assertJsonPath('data.internal_name', 'Updated Detail')
            ->assertJsonPath('data.backward_compatibility', 'UD123');
    }

    public function test_update_returns_the_expected_data_including_item_data(): void
    {
        $detail = Detail::factory()->for(Item::factory())->create();
        $other_item = Item::factory()->create();

        $response = $this->putJson(route('detail.update', $detail->id), [
                'item_id' => $other_item->id,
                'internal_name' => 'Updated Detail',
                'backward_compatibility' => 'UD123',
            ]);

        $response->assertJsonPath('data.item.id', $other_item->id)
            ->assertJsonPath('data.item.internal_name', $other_item->internal_name)
            ->assertJsonPath('data.item.backward_compatibility', $other_item->backward_compatibility);
    }
}

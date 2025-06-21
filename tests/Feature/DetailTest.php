<?php

namespace Tests\Feature;

use App\Models\Detail;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DetailTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_detail_factory(): void
    {
        $detail = Detail::factory()->for(Item::factory())->create();
        $this->assertNotNull($detail->item_id);
        $this->assertDatabaseHas('details', [
            'id' => $detail->id,
            'internal_name' => $detail->internal_name,
            'backward_compatibility' => $detail->backward_compatibility,
        ]);
    }

    public function test_detail_factory_withitem(): void
    {
        $detail = Detail::factory()->withItem()->create();
        $this->assertNotNull($detail->item_id);
        $this->assertDatabaseHas('details', [
                'id' => $detail->id,
                'item_id' => $detail->item_id,
            ]);
    }

    public function test_api_authentication_index_forbids_anonymous_access(): void
    {
        $response = $this->getJson(route('detail.index'));
        $response->assertUnauthorized();
    }

    public function test_api_authentication_index_allows_authenticated_users(): void
    {
        $user = User::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->getJson(route('detail.index'));
        $response_authenticated->assertOk();
    }

    public function test_api_authentication_show_forbids_anonymous_access(): void
    {
        $detail = Detail::factory()->for(Item::factory())->create();
        $response = $this->getJson(route('detail.show', $detail->id));
        $response->assertUnauthorized();
    }

    public function test_api_authentication_show_allows_authenticated_users(): void
    {
        $user = User::factory()->create();
        $detail = Detail::factory()->for(Item::factory())->create();
        $response_authenticated = $this->actingAs($user)
            ->getJson(route('detail.show', $detail->id));
        $response_authenticated->assertOk();
    }

    public function test_api_authentication_store_forbids_anonymous_access(): void
    {
        $response = $this->postJson(route('detail.store'), [
            'item_id' => Item::Factory()->create()->id,
            'internal_name' => 'Test Detail',
            'backward_compatibility' => 'TD',
        ]);
        $response->assertUnauthorized();
    }

    public function test_api_authentication_store_allows_authenticated_users(): void
    {
        $user = User::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->postJson(route('detail.store'), [
                'item_id' => Item::Factory()->create()->id,
                'internal_name' => 'Test Detail',
                'backward_compatibility' => 'TD',
            ]);
        $response_authenticated->assertCreated();
    }

    public function test_api_authentication_update_forbids_anonymous_access(): void
    {
        $detail = Detail::factory()->for(Item::factory())->create();
        $response = $this->putJson(route('detail.update', $detail->id), [
            'item_id' => Item::Factory()->create()->id,
            'internal_name' => 'Updated Detail',
            'backward_compatibility' => 'UD',
        ]);
        $response->assertUnauthorized();
    }

    public function test_api_authentication_update_allows_authenticated_users(): void
    {
        $user = User::factory()->create();
        $detail = Detail::factory()->for(Item::factory())->create();
        $response_authenticated = $this->actingAs($user)
            ->putJson(route('detail.update', $detail->id), [
                'item_id' => Item::Factory()->create()->id,
                'internal_name' => 'Updated Detail',
                'backward_compatibility' => 'UD',
            ]);
        $response_authenticated->assertOk();
    }

    public function test_api_authentication_destroy_forbids_anonymous_access(): void
    {
        $detail = Detail::factory()->for(Item::factory())->create();
        $response = $this->deleteJson(route('detail.destroy', $detail->id));
        $response->assertUnauthorized();
    }

    public function test_api_authentication_destroy_allows_authenticated_users(): void
    {
        $user = User::factory()->create();
        $detail = Detail::factory()->for(Item::factory())->create();
        $response_authenticated = $this->actingAs($user)
            ->deleteJson(route('detail.destroy', $detail->id));
        $response_authenticated->assertNoContent();
    }

    public function test_api_response_show_returns_not_found_when_not_found(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('detail.show', 'nonexistent'));

        $response->assertNotFound();
    }

    public function test_api_response_show_returns_the_expected_structure(): void
    {
        $user = User::factory()->create();
        $detail = Detail::factory()->for(Item::factory())->create();
        $response = $this->actingAs($user)
            ->getJson(route('detail.show', $detail->id));

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

    public function test_api_response_show_returns_the_expected_structure_including_item_data(): void
    {
        $user = User::factory()->create();
        $detail = Detail::factory()->for(Item::factory())->create();
        $response = $this->actingAs($user)
            ->getJson(route('detail.show', $detail->id));

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

    public function test_api_response_show_returns_the_expected_data(): void
    {
        $user = User::factory()->create();
        $detail = Detail::factory()->for(Item::factory())->create();
        $response = $this->actingAs($user)
            ->getJson(route('detail.show', $detail->id));

        $response->assertJsonPath('data.id', $detail->id)
            ->assertJsonPath('data.internal_name', $detail->internal_name)
            ->assertJsonPath('data.backward_compatibility', $detail->backward_compatibility)
            ;
    }

    public function test_api_response_show_returns_the_expected_data_including_item_data(): void
    {
        $user = User::factory()->create();
        $detail = Detail::factory()->for(Item::factory())->create();
        $response = $this->actingAs($user)
            ->getJson(route('detail.show', $detail->id));

        $response->assertJsonPath('data.id', $detail->id)
            ->assertJsonPath('data.item.id', $detail->item->id)
            ->assertJsonPath('data.item.internal_name', $detail->item->internal_name)
            ->assertJsonPath('data.item.backward_compatibility', $detail->item->backward_compatibility)
            ;
    }

    public function test_api_response_index_returns_ok_when_no_data(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('detail.index'));

        $response->assertOk();
    }

    public function test_api_response_index_returns_an_empty_array_when_no_data(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('detail.index'));

        $response->assertJsonCount(0, 'data');
    }

    public function test_api_response_index_returns_the_expected_structure(): void
    {
        $user = User::factory()->create();
        $detail = Detail::factory()->for(Item::factory())->create();
        $response = $this->actingAs($user)
            ->getJson(route('detail.index'));

        $response->assertExactJsonStructure ([
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
        $user = User::factory()->create();
        $detail = Detail::factory()->for(Item::factory())->create();
        $response = $this->actingAs($user)
            ->getJson(route('detail.index'));

        $response->assertJsonStructure ([
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

    public function test_api_response_index_returns_the_expected_data() : void
    {
        $user = User::factory()->create();
        $detail1 = Detail::factory()->WithItem()->create();
        $detail2 = Detail::factory()->WithItem()->create();
        $response = $this->actingAs($user)
            ->getJson(route('detail.index'));

        $response->assertJsonPath('data.0.id', $detail1->id)
            ->assertJsonPath('data.0.internal_name', $detail1->internal_name)
            ->assertJsonPath('data.0.backward_compatibility', $detail1->backward_compatibility)
            ->assertJsonPath('data.1.id', $detail2->id)
            ->assertJsonPath('data.1.internal_name', $detail2->internal_name)
            ->assertJsonPath('data.1.backward_compatibility', $detail2->backward_compatibility);
    }

    public function test_api_process_store_validates_its_input(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->postJson(route('detail.store'), [
                'id' => 'invalid-id', // Invalid: prohibited field
                'item_id' => 'invalid_id', // Invalid: not a valid Item ID
                'internal_name' => '', // Invalid: required field
                'backward_compatibility' => null,
            ]);

        $response->assertJsonValidationErrors(['id', 'internal_name', 'item_id']);
    }

    public function test_api_response_store_returns_unprocessable_when_input_is_invalid(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->postJson(route('detail.store'), [
                'id' => 'invalid-id', // Invalid: prohibited field
                'item_id' => 'invalid_id', // Invalid: not a valid Item ID
                'internal_name' => '', // Invalid: required field
                'backward_compatibility' => 'TD',
            ]);

        $response->assertUnprocessable();
    }

    public function test_api_process_store_inserts_a_row(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('detail.store'), [
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

    public function test_api_response_store_returns_created_on_success(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('detail.store'), [
                'item_id' => $item->id,
                'internal_name' => 'Test Detail',
                'backward_compatibility' => 'TD',
            ]);

        $response->assertCreated();
    }

    public function test_api_response_store_returns_the_expected_structure(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('detail.store'), [
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

    public function test_api_response_store_returns_the_expected_structure_including_item_data(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('detail.store'), [
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

    public function test_api_response_store_returns_the_expected_data(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('detail.store'), [
                'item_id' => Item::Factory()->create()->id,
                'internal_name' => 'Test Detail',
                'backward_compatibility' => 'TD',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.internal_name', 'Test Detail')
            ->assertJsonPath('data.backward_compatibility', 'TD');
    }

    public function test_api_response_store_returns_the_expected_data_including_item_data(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('detail.store'), [
                'item_id' => $item->id,
                'internal_name' => 'Test Detail',
                'backward_compatibility' => 'TD',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.item.id', $item->id)
            ->assertJsonPath('data.item.internal_name', $item->internal_name)
            ->assertJsonPath('data.item.backward_compatibility', $item->backward_compatibility);
    }
    
    public function test_api_process_update_validates_its_input(): void
    {
        $user = User::factory()->create();
        $detail = Detail::factory()->for(Item::factory())->create();

        $response = $this->actingAs($user)
            ->putJson(route('detail.update', $detail->id), [
                'id' => 'invalid-id', // Invalid: prohibited field
                'item_id' => 'invalid_id', // Invalid: not a valid Item ID
                'internal_name' => '', // Invalid: required field
                'backward_compatibility' => 'UD',
            ]);

        $response->assertJsonValidationErrors(['id', 'internal_name', 'item_id']);
    }

    public function test_api_response_update_returns_not_found_response_when_not_found(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->putJson(route('detail.update', 'nonexistent'), [
                'item_id' => Item::Factory()->create()->id,
                'internal_name' => 'Updated Detail',
                'backward_compatibility' => 'UD123',
            ]);

        $response->assertNotFound();
    }

    public function test_api_process_update_updates_a_row(): void
    {
        $user = User::factory()->create();
        $detail = Detail::factory()->for(Item::factory())->create();
        $other_item = Item::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('detail.update', $detail->id), [
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

    public function test_api_response_update_returns_ok_on_success(): void
    {
        $user = User::factory()->create();
        $detail = Detail::factory()->for(Item::factory())->create();

        $response = $this->actingAs($user)
            ->putJson(route('detail.update', $detail->id), [
                'item_id' => $detail->item_id,
                'internal_name' => 'Updated Detail',
                'backward_compatibility' => 'UD123',
            ]);

        $response->assertOk();
    }

    public function test_api_response_update_returns_unprocessable_when_input_is_invalid(): void
    {
        $user = User::factory()->create();
        $detail = Detail::factory()->for(Item::factory())->create();

        $response = $this->actingAs($user)
            ->putJson(route('detail.update', $detail->id), [
                'id' => 'invalid-id', // Invalid: prohibited field
                'item_id' => 'invalid_id', // Invalid: not a valid Item ID
                'internal_name' => '', // Invalid: required field
                'backward_compatibility' => 'UD',
            ]);

        $response->assertUnprocessable();
    }

    public function test_api_response_update_returns_the_expected_structure(): void
    {
        $user = User::factory()->create();
        $detail = Detail::factory()->for(Item::factory())->create();

        $response = $this->actingAs($user)
            ->putJson(route('detail.update', $detail->id), [
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

    public function test_api_response_update_returns_the_expected_structure_including_item_data(): void
    {
        $user = User::factory()->create();
        $detail = Detail::factory()->for(Item::factory())->create();
        $item = Item::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('detail.update', $detail->id), [
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

    public function test_api_response_update_returns_the_expected_data(): void
    {
        $user = User::factory()->create();
        $detail = Detail::factory()->for(Item::factory())->create();

        $response = $this->actingAs($user)
            ->putJson(route('detail.update', $detail->id), [
                'item_id' => $detail->item_id,
                'internal_name' => 'Updated Detail',
                'backward_compatibility' => 'UD123',
            ]);

        $response->assertJsonPath('data.internal_name', 'Updated Detail')
            ->assertJsonPath('data.backward_compatibility', 'UD123');
    }

    public function test_api_response_update_returns_the_expected_data_including_item_data(): void
    {
        $user = User::factory()->create();
        $detail = Detail::factory()->for(Item::factory())->create();
        $other_item = Item::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('detail.update', $detail->id), [
                'item_id' => $other_item->id,
                'internal_name' => 'Updated Detail',
                'backward_compatibility' => 'UD123',
            ]);

        $response->assertJsonPath('data.item.id', $other_item->id)
            ->assertJsonPath('data.item.internal_name', $other_item->internal_name)
            ->assertJsonPath('data.item.backward_compatibility', $other_item->backward_compatibility);
    }

    public function test_api_response_destroy_returns_not_found_response_when_not_found(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->deleteJson(route('detail.destroy', 'nonexistent'));

        $response->assertNotFound();
    }

    public function test_api_process_destroy_deletes_a_row(): void
    {
        $user = User::factory()->create();
        $detail = Detail::factory()->for(Item::factory())->create();

        $this->actingAs($user)
            ->deleteJson(route('detail.destroy', $detail->id));

        $this->assertDatabaseMissing('details', ['id' => $detail->id]);
    }

    public function test_api_response_destroy_returns_no_content_on_success(): void
    {
        $user = User::factory()->create();
        $detail = Detail::factory()->for(Item::factory())->create();

        $response = $this->actingAs($user)
            ->deleteJson(route('detail.destroy', $detail->id));

        $response->assertNoContent();
    }
}

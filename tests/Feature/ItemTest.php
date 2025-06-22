<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Item;
use App\Models\Partner;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_item_factory(): void
    {
        $item = Item::factory()->create();
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'internal_name' => $item->internal_name,
            'backward_compatibility' => $item->backward_compatibility,
            'partner_id' => null,
            'country_id' => null,
            'project_id' => null,
            'type' => $item->type,
        ]);
    }

    public function test_item_factory_object(): void
    {
        $item = Item::factory()->Object()->create();
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'type' => 'object',
        ]);
    }

    public function test_item_factory_monument(): void
    {
        $item = Item::factory()->Monument()->create();
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'type' => 'monument',
        ]);
    }

    public function test_item_factory_with_partner(): void
    {
        $item = Item::factory()->withPartner()->create();
        $this->assertNotNull($item->partner_id);
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'partner_id' => $item->partner->id,
        ]);
    }

    public function test_item_factory_with_country(): void
    {
        $item = Item::factory()->withCountry()->create();
        $this->assertNotNull($item->country_id);
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'country_id' => $item->country->id,
        ]);
    }

    public function test_item_factory_with_project(): void
    {
        $item = Item::factory()->withProject()->create();
        $this->assertNotNull($item->project_id);
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'project_id' => $item->project->id,
        ]);
    }

    public function test_api_authentication_index_forbids_anonymous_access(): void
    {
        $response = $this->getJson(route('item.index'));
        $response->assertUnauthorized();
    }

    public function test_api_authentication_index_allows_authenticated_users(): void
    {
        $user = User::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->getJson(route('item.index'));
        $response_authenticated->assertOk();
    }

    public function test_api_authentication_show_forbids_anonymous_access(): void
    {
        $item = Item::factory()->create();
        $response = $this->getJson(route('item.show', $item->id));
        $response->assertUnauthorized();
    }

    public function test_api_authentication_show_allows_authenticated_users(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->getJson(route('item.show', $item->id));
        $response_authenticated->assertOk();
    }

    public function test_api_authentication_store_forbids_anonymous_access(): void
    {
        $response = $this->postJson(route('item.store'), [
            'partner_id' => Partner::factory()->create()->id,
            'internal_name' => 'Test Item',
            'backward_compatibility' => 'TI',
            'type' => 'object',
        ]);
        $response->assertUnauthorized();
    }

    public function test_api_authentication_store_allows_authenticated_users(): void
    {
        $user = User::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->postJson(route('item.store'), [
                'partner_id' => Partner::factory()->create()->id,
                'internal_name' => 'Test Item',
                'backward_compatibility' => 'TI',
                'type' => 'object',
            ]);
        $response_authenticated->assertCreated();
    }

    public function test_api_authentication_update_forbids_anonymous_access(): void
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

    public function test_api_authentication_update_allows_authenticated_users(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->putJson(route('item.update', $item->id), [
                'partner_id' => Partner::factory()->create()->id,
                'internal_name' => 'Updated Item',
                'backward_compatibility' => 'UI',
                'type' => 'monument',
            ]);
        $response_authenticated->assertOk();
    }

    public function test_api_authentication_destroy_forbids_anonymous_access(): void
    {
        $item = Item::factory()->create();
        $response = $this->deleteJson(route('item.destroy', $item->id));
        $response->assertUnauthorized();
    }

    public function test_api_authentication_destroy_allows_authenticated_users(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->deleteJson(route('item.destroy', $item->id));
        $response_authenticated->assertNoContent();
    }

    public function test_api_response_show_returns_not_found_when_not_found(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('item.show', 'nonexistent'));

        $response->assertNotFound();
    }

    public function test_api_response_show_returns_the_expected_structure(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('item.show', $item->id));

        $response->assertJsonStructure([
            'data' => [
                'id',
                'partner',
                'internal_name',
                'backward_compatibility',
                'type',
                'country',
                'project',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_api_response_show_returns_the_expected_structure_including_partner_data(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->withPartner()->create();
        $response = $this->actingAs($user)
            ->getJson(route('item.show', $item->id));

        $response->assertJsonStructure([
            'data' => [
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
        ]);
    }

    public function test_api_response_show_returns_the_expected_structure_including_country_data(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->withCountry()->create();
        $response = $this->actingAs($user)
            ->getJson(route('item.show', $item->id));

        $response->assertJsonStructure([
            'data' => [
                'country' => [
                    'id',
                    'internal_name',
                    'backward_compatibility',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    public function test_api_response_show_returns_the_expected_structure_including_project_data(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->WithProject()->create();
        $response = $this->actingAs($user)
            ->getJson(route('item.show', $item->id));

        $response->assertJsonStructure([
            'data' => [
                'project' => [
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
        $item = Item::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('item.show', $item->id));

        $response->assertJsonPath('data.id', $item->id)
            ->assertJsonPath('data.internal_name', $item->internal_name)
            ->assertJsonPath('data.backward_compatibility', $item->backward_compatibility)
            ->assertJsonPath('data.type', $item->type);
    }

    public function test_api_response_show_returns_the_expected_data_including_partner_data(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->withPartner()->create();
        $response = $this->actingAs($user)
            ->getJson(route('item.show', $item->id));

        $response->assertJsonPath('data.id', $item->id)
            ->assertJsonPath('data.partner.id', $item->partner->id)
            ->assertJsonPath('data.partner.internal_name', $item->partner->internal_name)
            ->assertJsonPath('data.partner.backward_compatibility', $item->partner->backward_compatibility);
    }

    public function test_api_response_show_returns_the_expected_data_including_country_data(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->withCountry()->create();
        $response = $this->actingAs($user)
            ->getJson(route('item.show', $item->id));

        $response->assertJsonPath('data.id', $item->id)
            ->assertJsonPath('data.country.id', $item->country->id)
            ->assertJsonPath('data.country.internal_name', $item->country->internal_name)
            ->assertJsonPath('data.country.backward_compatibility', $item->country->backward_compatibility);
    }

    public function test_api_response_show_returns_the_expected_data_including_project_data(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->withProject()->create();
        $response = $this->actingAs($user)
            ->getJson(route('item.show', $item->id));

        $response->assertJsonPath('data.id', $item->id)
            ->assertJsonPath('data.project.id', $item->project->id)
            ->assertJsonPath('data.project.internal_name', $item->project->internal_name)
            ->assertJsonPath('data.project.backward_compatibility', $item->project->backward_compatibility);
    }

    public function test_api_response_index_returns_ok_when_no_data(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('item.index'));

        $response->assertOk();
    }

    public function test_api_response_index_returns_an_empty_array_when_no_data(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('item.index'));

        $response->assertJsonCount(0, 'data');
    }

    public function test_api_response_index_returns_the_expected_structure(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('item.index'));

        $response->assertExactJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'partner',
                    'internal_name',
                    'backward_compatibility',
                    'type',
                    'country',
                    'project',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    public function test_api_response_index_returns_the_expected_structure_including_partner_data(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->WithPartner()->create();
        $response = $this->actingAs($user)
            ->getJson(route('item.index'));

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

    public function test_api_response_index_returns_the_expected_structure_including_country_data(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->WithCountry()->create();
        $response = $this->actingAs($user)
            ->getJson(route('item.index'));

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

    public function test_api_response_index_returns_the_expected_structure_including_project_data(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->WithProject()->create();
        $response = $this->actingAs($user)
            ->getJson(route('item.index'));

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

    public function test_api_response_index_returns_the_expected_data(): void
    {
        $user = User::factory()->create();
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('item.index'));

        $response->assertJsonPath('data.0.id', $item1->id)
            ->assertJsonPath('data.0.internal_name', $item1->internal_name)
            ->assertJsonPath('data.0.backward_compatibility', $item1->backward_compatibility)
            ->assertJsonPath('data.0.type', $item1->type)
            ->assertJsonPath('data.1.id', $item2->id)
            ->assertJsonPath('data.1.internal_name', $item2->internal_name)
            ->assertJsonPath('data.1.backward_compatibility', $item2->backward_compatibility)
            ->assertJsonPath('data.1.type', $item2->type);
    }

    public function test_api_process_store_validates_its_input(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->postJson(route('item.store'), [
                'id' => 'invalid-id', // Invalid: prohibited field
                'partner_id' => null,
                'internal_name' => '', // Invalid: required field
                'backward_compatibility' => null,
                'type' => 'invalid_type', // Invalid: not in allowed types
            ]);

        $response->assertJsonValidationErrors(['id', 'internal_name', 'type']);
    }

    public function test_api_response_store_returns_unprocessable_when_input_is_invalid(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->postJson(route('item.store'), [
                'id' => 'invalid-id', // Invalid: prohibited field
                'partner_id' => null,
                'internal_name' => '', // Invalid: required field
                'backward_compatibility' => 'TI',
                'type' => 'invalid_type', // Invalid: not in allowed types
            ]);

        $response->assertUnprocessable();
    }

    public function test_api_process_store_inserts_a_row(): void
    {
        $user = User::factory()->create();
        $partner = Partner::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('item.store'), [
                'partner_id' => $partner->id,
                'internal_name' => 'Test Item',
                'backward_compatibility' => 'TI',
                'type' => 'object',
            ]);

        $this->assertDatabaseHas('items', [
            'internal_name' => 'Test Item',
            'backward_compatibility' => 'TI',
            'type' => 'object',
            'partner_id' => $partner->id,
        ]);
    }

    public function test_api_response_store_returns_created_on_success(): void
    {
        $user = User::factory()->create();
        $partner = Partner::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('item.store'), [
                'partner_id' => $partner->id,
                'internal_name' => 'Test Item',
                'backward_compatibility' => 'TI',
                'type' => 'object',
            ]);

        $response->assertCreated();
    }

    public function test_api_response_store_returns_the_expected_structure(): void
    {
        $user = User::factory()->create();
        $partner = Partner::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('item.store'), [
                'partner_id' => $partner->id,
                'internal_name' => 'Test Item',
                'backward_compatibility' => 'TI',
                'type' => 'object',
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'partner',
                    'internal_name',
                    'backward_compatibility',
                    'type',
                    'country',
                    'project',
                    'created_at',
                    'updated_at',
                ],
            ]);
    }

    public function test_api_response_store_returns_the_expected_structure_including_partner_data(): void
    {
        $user = User::factory()->create();
        $partner = Partner::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('item.store'), [
                'partner_id' => $partner->id,
                'internal_name' => 'Test Item',
                'backward_compatibility' => 'TI',
                'type' => 'object',
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
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
            ]);
    }

    public function test_api_response_store_returns_the_expected_structure_including_country_data(): void
    {
        $user = User::factory()->create();
        $partner = Partner::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('item.store'), [
                'partner_id' => $partner->id,
                'internal_name' => 'Test Item',
                'backward_compatibility' => 'TI',
                'type' => 'object',
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'country' => [
                        'id',
                        'internal_name',
                        'backward_compatibility',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ]);
    }

    public function test_api_response_store_returns_the_expected_structure_including_project_data(): void
    {
        $user = User::factory()->create();
        $partner = Partner::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('item.store'), [
                'partner_id' => $partner->id,
                'internal_name' => 'Test Item',
                'backward_compatibility' => 'TI',
                'type' => 'object',
            ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'project' => [
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
            ->postJson(route('item.store'), [
                'partner_id' => Partner::factory()->create()->id,
                'internal_name' => 'Test Item',
                'backward_compatibility' => 'TI',
                'type' => 'object',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.internal_name', 'Test Item')
            ->assertJsonPath('data.backward_compatibility', 'TI')
            ->assertJsonPath('data.type', 'object');
    }

    public function test_api_response_store_returns_the_expected_data_including_partner_data(): void
    {
        $user = User::factory()->create();
        $partner = Partner::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('item.store'), [
                'partner_id' => $partner->id,
                'internal_name' => 'Test Item',
                'backward_compatibility' => 'TI',
                'type' => 'object',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.partner.id', $partner->id)
            ->assertJsonPath('data.partner.internal_name', $partner->internal_name)
            ->assertJsonPath('data.partner.backward_compatibility', $partner->backward_compatibility);
    }

    public function test_api_response_store_returns_the_expected_data_including_country_data(): void
    {
        $user = User::factory()->create();
        $partner = Partner::factory()->create();
        $country = Country::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('item.store'), [
                'partner_id' => $partner->id,
                'country_id' => $country->id,
                'internal_name' => 'Test Item',
                'backward_compatibility' => 'TI',
                'type' => 'object',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.country.id', $country->id)
            ->assertJsonPath('data.country.internal_name', $country->internal_name)
            ->assertJsonPath('data.country.backward_compatibility', $country->backward_compatibility);
    }

    public function test_api_response_store_returns_the_expected_data_including_project_data(): void
    {
        $user = User::factory()->create();
        $partner = Partner::factory()->create();
        $project = Project::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('item.store'), [
                'partner_id' => $partner->id,
                'project_id' => $project->id,
                'internal_name' => 'Test Item',
                'backward_compatibility' => 'TI',
                'type' => 'object',
            ]);

        $response->assertCreated()
            ->assertJsonPath('data.project.id', $project->id)
            ->assertJsonPath('data.project.internal_name', $project->internal_name)
            ->assertJsonPath('data.project.backward_compatibility', $project->backward_compatibility);
    }

    public function test_api_process_update_validates_its_input(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('item.update', $item->id), [
                'id' => 'invalid-id', // Invalid: prohibited field
                'partner_id' => null,
                'internal_name' => '', // Invalid: required field
                'backward_compatibility' => 'UI',
                'type' => 'invalid_type', // Invalid: not in allowed types
            ]);

        $response->assertJsonValidationErrors(['id', 'internal_name', 'type']);
    }

    public function test_api_response_update_returns_not_found_response_when_not_found(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->putJson(route('item.update', 'nonexistent'), [
                'partner_id' => Partner::factory()->create()->id,
                'internal_name' => 'Updated Item',
                'backward_compatibility' => 'UI123',
                'type' => 'monument',
            ]);

        $response->assertNotFound();
    }

    public function test_api_process_update_updates_a_row(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $partner = Partner::factory()->create();

        $this->actingAs($user)
            ->putJson(route('item.update', $item->id), [
                'partner_id' => $partner->id,
                'internal_name' => 'Updated Item',
                'backward_compatibility' => 'UI123',
                'type' => 'monument',
            ]);

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'partner_id' => $partner->id,
            'internal_name' => 'Updated Item',
            'backward_compatibility' => 'UI123',
            'type' => 'monument',
        ]);
    }

    public function test_api_response_update_returns_ok_on_success(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('item.update', $item->id), [
                'partner_id' => $item->partner_id,
                'internal_name' => 'Updated Item',
                'backward_compatibility' => 'UI123',
                'type' => 'monument',
            ]);

        $response->assertOk();
    }

    public function test_api_response_update_returns_unprocessable_when_input_is_invalid(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('item.update', $item->id), [
                'id' => 'invalid-id', // Invalid: prohibited field
                'partner_id' => 'invalid_id', // Invalid: not a valid Partner ID
                'internal_name' => '', // Invalid: required field
                'backward_compatibility' => 'UI',
                'type' => 'invalid_type', // Invalid: not in allowed types
            ]);

        $response->assertUnprocessable();
    }

    public function test_api_response_update_returns_the_expected_structure(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('item.update', $item->id), [
                'partner_id' => $item->partner_id,
                'internal_name' => 'Updated Item',
                'backward_compatibility' => 'UI123',
                'type' => 'monument',
            ]);

        $response->assertJsonStructure([
            'data' => [
                'id',
                'partner',
                'internal_name',
                'backward_compatibility',
                'type',
                'country',
                'project',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_api_response_update_returns_the_expected_structure_including_partner_data(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->withPartner()->create();
        $partner = Partner::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('item.update', $item->id), [
                'partner_id' => $partner->id,
                'internal_name' => 'Updated Item',
                'backward_compatibility' => 'UI123',
                'type' => 'monument',
            ]);

        $response->assertJsonStructure([
            'data' => [
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
        ]);
    }

    public function test_api_response_update_returns_the_expected_structure_including_country_data(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $country = Country::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('item.update', $item->id), [
                'partner_id' => $item->partner_id,
                'country_id' => $country->id,
                'internal_name' => 'Updated Item',
                'backward_compatibility' => 'UI123',
                'type' => 'monument',
            ]);

        $response->assertJsonStructure([
            'data' => [
                'country' => [
                    'id',
                    'internal_name',
                    'backward_compatibility',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);
    }

    public function test_api_response_update_returns_the_expected_structure_including_project_data(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $project = Project::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('item.update', $item->id), [
                'partner_id' => $item->partner_id,
                'project_id' => $project->id,
                'internal_name' => 'Updated Item',
                'backward_compatibility' => 'UI123',
                'type' => 'monument',
            ]);

        $response->assertJsonStructure([
            'data' => [
                'project' => [
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
        $item = Item::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('item.update', $item->id), [
                'partner_id' => $item->partner_id,
                'internal_name' => 'Updated Item',
                'backward_compatibility' => 'UI123',
                'type' => 'monument',
            ]);

        $response->assertJsonPath('data.internal_name', 'Updated Item')
            ->assertJsonPath('data.backward_compatibility', 'UI123')
            ->assertJsonPath('data.type', 'monument');
    }

    public function test_api_response_update_returns_the_expected_data_including_partner_data(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $partner = Partner::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('item.update', $item->id), [
                'partner_id' => $partner->id,
                'internal_name' => 'Updated Item',
                'backward_compatibility' => 'UI123',
                'type' => 'monument',
            ]);

        $response->assertJsonPath('data.partner.id', $partner->id)
            ->assertJsonPath('data.partner.internal_name', $partner->internal_name)
            ->assertJsonPath('data.partner.backward_compatibility', $partner->backward_compatibility);
    }

    public function test_api_response_update_returns_the_expected_data_including_country_data(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $country = Country::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('item.update', $item->id), [
                'partner_id' => $item->partner_id,
                'country_id' => $country->id,
                'internal_name' => 'Updated Item',
                'backward_compatibility' => 'UI123',
                'type' => 'monument',
            ]);

        $response->assertJsonPath('data.country.id', $country->id)
            ->assertJsonPath('data.country.internal_name', $country->internal_name)
            ->assertJsonPath('data.country.backward_compatibility', $country->backward_compatibility);
    }

    public function test_api_response_update_returns_the_expected_data_including_project_data(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();
        $project = Project::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('item.update', $item->id), [
                'partner_id' => $item->partner_id,
                'project_id' => $project->id,
                'internal_name' => 'Updated Item',
                'backward_compatibility' => 'UI123',
                'type' => 'monument',
            ]);

        $response->assertJsonPath('data.project.id', $project->id)
            ->assertJsonPath('data.project.internal_name', $project->internal_name)
            ->assertJsonPath('data.project.backward_compatibility', $project->backward_compatibility);
    }

    public function test_api_response_destroy_returns_not_found_response_when_not_found(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->deleteJson(route('item.destroy', 'nonexistent'));

        $response->assertNotFound();
    }

    public function test_api_process_destroy_deletes_a_row(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $this->actingAs($user)
            ->deleteJson(route('item.destroy', $item->id));

        $this->assertDatabaseMissing('items', ['id' => $item->id]);
    }

    public function test_api_response_destroy_returns_no_content_on_success(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->create();

        $response = $this->actingAs($user)
            ->deleteJson(route('item.destroy', $item->id));

        $response->assertNoContent();
    }
}

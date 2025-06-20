<?php

namespace Tests\Feature;

use App\Models\Partner;
use App\Models\Country;
use App\Models\Project;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ItemTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_index_requires_authentication(): void
    {
        $response_anonymous = $this->getJson(route('item.index'));
        $response_anonymous->assertUnauthorized();

        $user = User::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->getJson(route('item.index'));
        $response_authenticated->assertOk();
    }

    public function test_show_requires_authentication(): void
    {
        $item = Item::factory()->Object()->withPartner()->create();

        $response_anonymous = $this->getJson(route('item.show', $item->id));
        $response_anonymous->assertUnauthorized();

        $user = User::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->getJson(route('item.show', $item->id));
        $response_authenticated->assertOk();
    }

    public function test_store_requires_authentication(): void
    {
        $response_anonymous = $this->postJson(route('item.store'), [
            'partner_id' => Partner::factory()->create()->id,
            'internal_name' => 'Test Item',
            'backward_compatibility' => 'TI',
            'type' => 'object',
        ]);
        $response_anonymous->assertUnauthorized();

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

    public function test_update_requires_authentication(): void
    {
        $item = Item::factory()->Object()->withPartner()->create();

        $response_anonymous = $this->putJson(route('item.update', $item->id), [
            'partner_id' => Partner::factory()->create()->id,
            'internal_name' => 'Updated Item',
            'backward_compatibility' => 'UI',
            'type' => 'monument',
        ]);
        $response_anonymous->assertUnauthorized();

        $user = User::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->putJson(route('item.update', $item->id), [
                'partner_id' => Partner::factory()->create()->id,
                'internal_name' => 'Updated Item',
                'backward_compatibility' => 'UI',
                'type' => 'monument',
            ]);
        $response_authenticated->assertOk();
    }

    public function test_destroy_requires_authentication(): void
    {
        $item = Item::factory()->withPartner()->create();

        $response_anonymous = $this->deleteJson(route('item.destroy', $item->id));
        $response_anonymous->assertUnauthorized();

        $user = User::factory()->create();
        $response_authenticated = $this->actingAs($user)
            ->deleteJson(route('item.destroy', $item->id));
        $response_authenticated->assertNoContent();
    }

    public function test_show_returns_a_well_structured_response(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->Object()->withPartner()->create();
        $response = $this->actingAs($user)
            ->getJson(route('item.show', $item->id));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'internal_name',
                    'backward_compatibility',
                    'type',
                ],
            ])
            ->assertJsonFragment([
                'id' => $item->id,
                'internal_name' => $item->internal_name,
                'backward_compatibility' => $item->backward_compatibility,
                'type' => $item->type,
            ])
            ->assertJsonFragment([
                'id' => $item->partner->id,
            ]);
    }

    public function test_index_returns_a_well_structured_response(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->Object()->withPartner()->create();
        $response = $this->actingAs($user)
            ->getJson(route('item.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'internal_name',
                        'backward_compatibility',
                        'type',
                        'partner' => [
                            'id',
                            'internal_name',
                        ],
                    ],
                ],
            ])
            ->assertJsonFragment([
                'id' => $item->id,
                'internal_name' => $item->internal_name,
                'backward_compatibility' => $item->backward_compatibility,
                'type' => $item->type,
            ]);
    }

    public function test_store_returns_a_well_structured_response(): void
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
                    'internal_name',
                    'backward_compatibility',
                    'type',
                    'project' => [
                        'id',
                        'internal_name',
                    ],
                ],
            ])
            ->assertJsonFragment([
                'internal_name' => 'Test Item',
                'backward_compatibility' => 'TI',
                'type' => 'object',
            ])
            ->assertJsonFragment([
                'id' => $partner->id,
                'internal_name' => $partner->internal_name,
            ]);
    }

    public function test_update_returns_a_well_structured_response(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->Object()->withPartner()->create();
        $other_partner = Partner::factory()->create();

        $response = $this->actingAs($user)
            ->putJson(route('item.update', $item->id), [
                'internal_name' => 'Updated Item',
                'backward_compatibility' => 'UI123',
                'type' => 'monument',
            ]);
        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'internal_name',
                    'backward_compatibility',
                    'type',
                    'partner' => [
                        'id',
                        'internal_name',
                    ],
                ],
            ])
            ->assertJsonFragment([
                'id' => $item->id,
                'internal_name' => 'Updated Item',
                'backward_compatibility' => 'UI123',
                'type' => 'monument',
            ])
            ->assertJsonFragment([
                'id' => $other_partner->id,
                'internal_name' => $other_partner->internal_name,
            ]);
    }

    public function test_destroy_returns_no_content(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->Object()->withPartner()->create();

        $response = $this->actingAs($user)
            ->deleteJson(route('item.destroy', $item->id));

        $response->assertNoContent();
        $this->assertDatabaseMissing('items', ['id' => $item->id]);
    }

    public function test_index_returns_empty_response_when_no_data(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('item.index'));

        $response->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_show_returns_not_found_response_when_not_found(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->getJson(route('item.show', 'nonexistent'));

        $response->assertNotFound();
    }

    public function test_update_returns_not_found_response_when_not_found(): void
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

    public function test_destroy_returns_not_found_response_when_not_found(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->deleteJson(route('item.destroy', 'nonexistent'));

        $response->assertNotFound();
    }

    public function test_store_returns_unprocessable_and_adequate_validation_errors(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)
            ->postJson(route('item.store'), [
                'id' => 'invalid-id', // Invalid: prohibited field
                'partner_id' => 'invalid_id', // Invalid: not a valid Partner ID
                'internal_name' => '', // Invalid: required field
                'backward_compatibility' => 'TI',
                'type' => 'invalid_type', // Invalid: not in allowed types
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['id', 'internal_name', 'partner_id', 'type']);
    }

    public function test_update_returns_unprocessable_and_adequate_validation_errors(): void
    {
        $user = User::factory()->create();
        $item = Item::factory()->withPartner()->create();

        $response = $this->actingAs($user)
            ->putJson(route('item.update', $item->id), [
                'id' => 'invalid-id', // Invalid: prohibited field
                'partner_id' => 'invalid_id', // Invalid: not a valid Partner ID
                'internal_name' => '', // Invalid: required field
                'backward_compatibility' => 'TI',
                'type' => 'invalid_type', // Invalid: not in allowed types
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['id', 'internal_name', 'partner_id', 'type']);
    }

    public function test_store_item_as_object_creates_item_with_correct_type(): void
    {
        $user = User::factory()->create();
        $partner = Partner::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('item.store'), [
                'partner_id' => $partner->id,
                'internal_name' => 'Test Object Item',
                'backward_compatibility' => 'TOI',
                'type' => 'object',
            ]);

        $response->assertCreated()
            ->assertJsonFragment([
                'data' => [
                    'partner_id' => $partner->id,
                    'internal_name' => 'Test Object Item',
                    'backward_compatibility' => 'TOI',
                    'type' => 'object',
                    'partner' => [
                        'id' => $partner->id,
                        'internal_name' => $partner->internal_name,
                    ],
                ],
            ]);
    }

    public function test_store_item_as_monument_creates_item_with_correct_type(): void
    {
        $user = User::factory()->create();
        $partner = Partner::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('item.store'), [
                'partner_id' => $partner->id,
                'internal_name' => 'Test Monument Item',
                'backward_compatibility' => 'TMI',
                'type' => 'monument',
            ]);

        $response->assertCreated()
            ->assertJsonFragment([
                'data' => [
                    'partner_id' => $partner->id,
                    'internal_name' => 'Test Monument Item',
                    'backward_compatibility' => 'TMI',
                    'type' => 'monument',
                    'partner' => [
                        'id' => $partner->id,
                        'internal_name' => $partner->internal_name,
                    ],
                ],
            ]);
    }

    public function test_store_item_with_invalid_type_returns_unprocessable(): void
    {
        $user = User::factory()->create();
        $partner = Partner::factory()->create();

        $response = $this->actingAs($user)
            ->postJson(route('item.store'), [
                'partner_id' => $partner->id,
                'internal_name' => 'Test Invalid Type Item',
                'backward_compatibility' => 'TITI',
                'type' => 'invalid_type', // Invalid type
            ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['type'])
            ->assertJsonFragment([
                'message' => 'The selected type is invalid.',
            ]);
    }
}

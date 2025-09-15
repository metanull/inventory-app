<?php

namespace Tests\Feature\Api\Item;

use App\Models\Item;
use App\Models\Partner;
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

    public function test_factory(): void
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

    public function test_factory_object(): void
    {
        $item = Item::factory()->Object()->create();
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'type' => 'object',
        ]);
    }

    public function test_factory_monument(): void
    {
        $item = Item::factory()->Monument()->create();
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'type' => 'monument',
        ]);
    }

    public function test_factory_with_partner(): void
    {
        $item = Item::factory()->withPartner()->create();
        $this->assertNotNull($item->partner_id);
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'partner_id' => $item->partner->id,
        ]);
    }

    public function test_factory_with_country(): void
    {
        $item = Item::factory()->withCountry()->create();
        $this->assertNotNull($item->country_id);
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'country_id' => $item->country->id,
        ]);
    }

    public function test_factory_with_project(): void
    {
        $item = Item::factory()->withProject()->create();
        $this->assertNotNull($item->project_id);
        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'project_id' => $item->project->id,
        ]);
    }

    public function test_store_allows_authenticated_users(): void
    {
        $response = $this->postJson(route('item.store'), [
            'partner_id' => Partner::factory()->create()->id,
            'internal_name' => 'Test Item',
            'backward_compatibility' => 'TI',
            'type' => 'object',
        ]);
        $response->assertCreated();
    }

    public function test_store_validates_its_input(): void
    {
        $response = $this->postJson(route('item.store'), [
            'id' => 'invalid-id', // Invalid: prohibited field
            'partner_id' => null,
            'internal_name' => '', // Invalid: required field
            'backward_compatibility' => null,
            'type' => 'invalid_type', // Invalid: not in allowed types
        ]);

        $response->assertJsonValidationErrors(['id', 'internal_name', 'type']);
    }

    public function test_store_returns_unprocessable_when_input_is_invalid(): void
    {
        $response = $this->postJson(route('item.store'), [
            'id' => 'invalid-id', // Invalid: prohibited field
            'partner_id' => null,
            'internal_name' => '', // Invalid: required field
            'backward_compatibility' => 'TI',
            'type' => 'invalid_type', // Invalid: not in allowed types
        ]);

        $response->assertUnprocessable();
    }

    public function test_store_inserts_a_row(): void
    {
        $partner = Partner::factory()->create();

        $response = $this->postJson(route('item.store', ['include' => 'partner']), [
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

    public function test_store_returns_created_on_success(): void
    {
        $partner = Partner::factory()->create();

        $response = $this->postJson(route('item.store'), [
            'partner_id' => $partner->id,
            'internal_name' => 'Test Item',
            'backward_compatibility' => 'TI',
            'type' => 'object',
        ]);

        $response->assertCreated();
    }

    public function test_store_returns_the_expected_structure(): void
    {
        $partner = Partner::factory()->create();

        $response = $this->postJson(route('item.store'), [
            'partner_id' => $partner->id,
            'internal_name' => 'Test Item',
            'backward_compatibility' => 'TI',
            'type' => 'object',
        ]);

        $response->assertJsonStructure([
            'data' => [
                'id',
                'internal_name',
                'backward_compatibility',
                'type',
                'owner_reference',
                'mwnf_reference',
                // relations are optional and returned only when included
                'partner',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_store_returns_the_expected_data(): void
    {
        $response = $this->postJson(route('item.store'), [
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
}

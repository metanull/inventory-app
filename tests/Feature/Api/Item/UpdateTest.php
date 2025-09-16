<?php

namespace Tests\Feature\Api\Item;

use App\Models\Item;
use App\Models\Partner;
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
        $item = Item::factory()->create();
        $response = $this->putJson(route('item.update', $item->id), [
            'partner_id' => Partner::factory()->create()->id,
            'internal_name' => 'Updated Item',
            'backward_compatibility' => 'UI',
            'type' => 'monument',
        ]);
        $response->assertOk();
    }

    public function test_update_validates_its_input(): void
    {
        $item = Item::factory()->create();

        $response = $this->putJson(route('item.update', $item->id), [
            'id' => 'invalid-id', // Invalid: prohibited field
            'partner_id' => null,
            'internal_name' => '', // Invalid: required field
            'backward_compatibility' => 'UI',
            'type' => 'invalid_type', // Invalid: not in allowed types
        ]);

        $response->assertJsonValidationErrors(['id', 'internal_name', 'type']);
    }

    public function test_update_returns_not_found_response_when_not_found(): void
    {
        $response = $this->putJson(route('item.update', 'nonexistent'), [
            'partner_id' => Partner::factory()->create()->id,
            'internal_name' => 'Updated Item',
            'backward_compatibility' => 'UI123',
            'type' => 'monument',
        ]);

        $response->assertNotFound();
    }

    public function test_update_updates_a_row(): void
    {
        $item = Item::factory()->create();
        $partner = Partner::factory()->create();

        $this->putJson(route('item.update', $item->id), [
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

    public function test_update_returns_ok_on_success(): void
    {
        $item = Item::factory()->create();

        $response = $this->putJson(route('item.update', $item->id), [
            'partner_id' => $item->partner_id,
            'internal_name' => 'Updated Item',
            'backward_compatibility' => 'UI123',
            'type' => 'monument',
        ]);

        $response->assertOk();
    }

    public function test_update_returns_unprocessable_when_input_is_invalid(): void
    {
        $item = Item::factory()->create();

        $response = $this->putJson(route('item.update', $item->id), [
            'id' => 'invalid-id', // Invalid: prohibited field
            'partner_id' => 'invalid_id', // Invalid: not a valid Partner ID
            'internal_name' => '', // Invalid: required field
            'backward_compatibility' => 'UI',
            'type' => 'invalid_type', // Invalid: not in allowed types
        ]);

        $response->assertUnprocessable();
    }

    public function test_update_returns_the_expected_structure(): void
    {
        $item = Item::factory()->create();

        $response = $this->putJson(route('item.update', $item->id), [
            'partner_id' => $item->partner_id,
            'internal_name' => 'Updated Item',
            'backward_compatibility' => 'UI123',
            'type' => 'monument',
        ]);

        $response->assertJsonStructure([
            'data' => [
                'id',
                'internal_name',
                'backward_compatibility',
                'type',
                'owner_reference',
                'mwnf_reference',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_update_returns_the_expected_data(): void
    {
        $item = Item::factory()->create();

        $response = $this->putJson(route('item.update', $item->id), [
            'partner_id' => $item->partner_id,
            'internal_name' => 'Updated Item',
            'backward_compatibility' => 'UI123',
            'type' => 'monument',
        ]);

        $response->assertJsonPath('data.internal_name', 'Updated Item')
            ->assertJsonPath('data.backward_compatibility', 'UI123')
            ->assertJsonPath('data.type', 'monument');
    }
}

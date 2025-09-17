<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Item;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_store_persists_item_and_redirects(): void
    {
        $payload = [
            'internal_name' => 'Test Item',
            'type' => 'object',
            'backward_compatibility' => 'LEG-1',
        ];

        $response = $this->post(route('items.store'), $payload);
        $response->assertRedirect();
        $this->assertDatabaseHas('items', [
            'internal_name' => 'Test Item',
            'type' => 'object',
        ]);
    }

    public function test_store_validation_errors(): void
    {
        $response = $this->post(route('items.store'), [
            'internal_name' => '',
            'type' => 'invalid',
        ]);
        $response->assertSessionHasErrors(['internal_name', 'type']);
    }

    public function test_store_rejects_invalid_country(): void
    {
        $response = $this->post(route('items.store'), [
            'internal_name' => 'Item X',
            'type' => 'object',
            'country_id' => 'ZZZ', // not seeded / invalid
        ]);
        $response->assertSessionHasErrors(['country_id']);
    }

    public function test_store_rejects_lowercase_country(): void
    {
        $response = $this->post(route('items.store'), [
            'internal_name' => 'Item Y',
            'type' => 'monument',
            'country_id' => 'fra', // lowercase triggers uppercase rule failure
        ]);
        $response->assertSessionHasErrors(['country_id']);
    }

    public function test_store_accepts_valid_country(): void
    {
        $country = \App\Models\Country::factory()->create(['id' => 'FRA']);
        $response = $this->post(route('items.store'), [
            'internal_name' => 'Item Z',
            'type' => 'object',
            'country_id' => $country->id,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('items', [
            'internal_name' => 'Item Z',
            'country_id' => $country->id,
        ]);
    }

    public function test_store_accepts_null_country(): void
    {
        $response = $this->post(route('items.store'), [
            'internal_name' => 'Item No Country',
            'type' => 'object',
            'country_id' => null,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('items', [
            'internal_name' => 'Item No Country',
            'country_id' => null,
        ]);
    }
}

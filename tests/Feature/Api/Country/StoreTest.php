<?php

namespace Tests\Feature\Api\Country;

use App\Models\Country;
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

    /**
     * Response: Assert store returns created on success.
     */
    public function test_store_returns_created_on_success()
    {
        $data = Country::factory()->make()->toArray();

        $response = $this->postJson(route('country.store'), $data);

        $response->assertCreated();
    }

    /**
     * Response: Assert store returns unprocessable entity when input is invalid.
     */
    public function test_store_returns_unprocessable_entity_when_input_is_invalid()
    {
        $response = $this->postJson(route('country.store'), []);

        $response->assertUnprocessable();
    }

    /**
     * Validation: Assert store validates its input.
     */
    public function test_store_validates_its_input()
    {
        $response = $this->postJson(route('country.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id', 'internal_name']);
    }

    /**
     * Authentication: Assert store allows authenticated users.
     */
    public function test_store_allows_authenticated_users()
    {
        $data = Country::factory()->make()->toArray();

        $response = $this->postJson(route('country.store'), $data);

        $response->assertCreated();
    }

    /**
     * Process: Assert store creates a row.
     */
    public function test_store_creates_a_row()
    {
        $data = Country::factory()->make()->toArray();

        $this->postJson(route('country.store'), $data);

        $this->assertDatabaseHas('countries', ['id' => $data['id']]);
    }

    /**
     * Validation: Assert store prevents duplicate key.
     */
    public function test_store_prevents_duplicate_key(): void
    {
        // First, create a country
        $existingCountry = Country::factory()->create([
            'id' => 'TST',
            'internal_name' => 'Test Country',
            'backward_compatibility' => 'TC',
        ]);

        // Try to create another country with the same ID (primary key)
        $response = $this->postJson(route('country.store'), [
            'id' => 'TST', // Same ID as existing country
            'internal_name' => 'Different Test Country',
            'backward_compatibility' => 'DC',
        ]);

        $response->assertUnprocessable();
    }
}

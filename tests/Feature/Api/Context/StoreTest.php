<?php

namespace Tests\Feature\Api\Context;

use App\Models\Context;
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
     * Authentication: store allows authenticated users.
     */
    public function test_api_authentication_store_allows_authenticated_users()
    {
        $data = Context::factory()->make()->except(['id','is_default']);

        $response = $this->postJson(route('context.store'), $data);
        $response->assertCreated();
    }

    /**
     * Process: store creates a row.
     */
    public function test_api_process_store_creates_a_row()
    {
        $data = Context::factory()->make()->except(['id','is_default']);

        $response = $this->postJson(route('context.store'), $data);
        $response->assertCreated();
        $this->assertDatabaseHas('contexts', $data);
    }

    /**
     * Response: store returns created on success.
     */
    public function test_api_response_store_returns_created_on_success()
    {
        $data = Context::factory()->make()->except(['id','is_default']);

        $response = $this->postJson(route('context.store'), $data);
        $response->assertCreated();
    }

    /**
     * Response: store returns unprocessable entity when input is invalid.
     */
    public function test_api_response_store_returns_unprocessable_entity_when_input_is_invalid()
    {
        $data = [
            // missing required fields
            'is_default' => true, // this field is prohibited in the request
        ];

        $response = $this->postJson(route('context.store'), $data);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name', 'is_default']);
    }

    /**
     * Response: store returns the expected structure.
     */
    public function test_api_response_store_returns_the_expected_structure()
    {
        $data = Context::factory()->make()->except(['id','is_default']);

        $response = $this->postJson(route('context.store'), $data);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'internal_name',
                'backward_compatibility',
                'is_default',
                'created_at',
                'updated_at',
            ],
        ]);
    }
    /**
     * Response: store returns the expected data.
     */
    public function test_api_response_store_returns_the_expected_data()
    {
        $data = Context::factory()->make()->except(['id','is_default']);

        $response = $this->postJson(route('context.store'), $data);
        $response->assertJsonPath('data.internal_name', $data['internal_name']);
        $response->assertJsonPath('data.backward_compatibility', $data['backward_compatibility']);
    }
    /**
     * Validation: store validates its input.
     */
    public function test_api_validation_store_validates_its_input()
    {
        $data = Context::factory()->make()->except(['internal_name']);  // missing required field: internal_name + 'is_default' is prohibited + 'id' is prohibited

        $response = $this->postJson(route('context.store'), $data);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name', 'is_default', 'id']);
    }
}
<?php

namespace Tests\Feature\Api\Context;

use App\Models\Context;
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

    /**
     * Authentication: update allows authenticated users.
     */
    public function test_api_authentication_update_allows_authenticated_users()
    {
        $context = Context::factory()->create();

        $data = [
            'internal_name' => $this->faker->unique()->word,
            'backward_compatibility' => $this->faker->word,
        ];

        $response = $this->putJson(route('context.update', $context), $data);
        $response->assertOk();
    }

    /**
     * Process: update updates a row.
     */
    public function test_api_process_update_updates_a_row()
    {
        $context = Context::factory()->create();

        $data = [
            'internal_name' => $this->faker->unique()->word,
            'backward_compatibility' => $this->faker->word,
        ];

        $response = $this->putJson(route('context.update', $context), $data);
        $response->assertOk();
        $this->assertDatabaseHas('contexts', ['id' => $context->id, 'internal_name' => $data['internal_name'], 'backward_compatibility' => $data['backward_compatibility']]);
    }

    /**
     * Response: update returns ok on success.
     */
    public function test_api_response_update_returns_ok_on_success()
    {
        $context = Context::factory()->create();

        $data = [
            'internal_name' => $this->faker->unique()->word,
            'backward_compatibility' => $this->faker->word,
        ];

        $response = $this->putJson(route('context.update', $context), $data);
        $response->assertOk();
    }

    /**
     * Response: update returns not found when record does not exist.
     */
    public function test_api_response_update_returns_not_found_when_record_does_not_exist()
    {
        $data = [
            'internal_name' => $this->faker->unique()->word,
            'backward_compatibility' => $this->faker->word,
        ];

        $response = $this->putJson(route('context.update', 'non-existent-id'), $data);
        $response->assertNotFound();
    }

    /**
     * Response: update returns unprocessable entity when input is invalid.
     */
    public function test_api_response_update_returns_unprocessable_entity_when_input_is_invalid()
    {
        $context = Context::factory()->create();

        $data = [
            // missing required fields
            'is_default' => true, // this field is prohibited in the request
        ];

        $response = $this->putJson(route('context.update', $context), $data);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name', 'is_default']);
    }

    
    /**
     * Response: update returns the expected structure.
     */
    public function test_api_response_update_returns_the_expected_structure()
    {
        $context = Context::factory()->create();
        
        $data = Context::factory()->make()->except(['id','is_default']);

        $response = $this->putJson(route('context.update', $context), $data);
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
     * Response: update returns the expected data.
     */
    public function test_api_response_update_returns_the_expected_data()
    {
        $context = Context::factory()->create();
        
        $data = Context::factory()->make()->except(['id','is_default']);

        $response = $this->putJson(route('context.update', $context), $data);
        $response->assertJsonPath('data.internal_name', $data['internal_name']);
        $response->assertJsonPath('data.backward_compatibility', $data['backward_compatibility']);
    }

    
    /**
     * Validation: update validates its input.
     */
    public function test_api_validation_update_validates_its_input()
    {
        $context = Context::factory()->create();
        $data = Context::factory()->make()->except(['internal_name']);  // missing required field: internal_name + 'is_default' is prohibited + 'id' is prohibited

        $response = $this->putJson(route('context.update', $context), $data);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name', 'is_default', 'id']);
    }

    
}
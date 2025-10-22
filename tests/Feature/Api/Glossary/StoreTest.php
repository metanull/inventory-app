<?php

namespace Tests\Feature\Api\Glossary;

use App\Models\Glossary;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class StoreTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user, 'sanctum');
    }

    /**
     * Authentication: store allows authenticated users.
     */
    public function test_store_allows_authenticated_users()
    {
        $data = Glossary::factory()->make()->except(['id']);

        $response = $this->postJson(route('glossary.store'), $data);
        $response->assertCreated();
    }

    /**
     * Process: store creates a row.
     */
    public function test_store_creates_a_row()
    {
        $data = Glossary::factory()->make()->except(['id']);

        $response = $this->postJson(route('glossary.store'), $data);
        $response->assertCreated();
        $this->assertDatabaseHas('glossaries', $data);
    }

    /**
     * Response: store returns created on success.
     */
    public function test_store_returns_created_on_success()
    {
        $data = Glossary::factory()->make()->except(['id']);

        $response = $this->postJson(route('glossary.store'), $data);
        $response->assertCreated();
    }

    /**
     * Response: store returns unprocessable entity when input is invalid.
     */
    public function test_store_returns_unprocessable_entity_when_input_is_invalid()
    {
        $data = [
            // missing required fields
            'id' => $this->faker->uuid(), // this field is prohibited in the request
        ];

        $response = $this->postJson(route('glossary.store'), $data);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name', 'id']);
    }

    /**
     * Response: store returns the expected structure.
     */
    public function test_store_returns_the_expected_structure()
    {
        $data = Glossary::factory()->make()->except(['id']);

        $response = $this->postJson(route('glossary.store'), $data);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'internal_name',
                'backward_compatibility',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    /**
     * Response: store returns the expected data.
     */
    public function test_store_returns_the_expected_data()
    {
        $data = Glossary::factory()->make()->except(['id']);

        $response = $this->postJson(route('glossary.store'), $data);
        $response->assertJsonPath('data.internal_name', $data['internal_name']);
        $response->assertJsonPath('data.backward_compatibility', $data['backward_compatibility']);
    }

    /**
     * Validation: store validates its input.
     */
    public function test_store_validates_its_input()
    {
        $data = Glossary::factory()->make()->except(['internal_name']);  // missing required field: internal_name
        $data['id'] = $this->faker->uuid(); // 'id' is prohibited

        $response = $this->postJson(route('glossary.store'), $data);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name', 'id']);
    }

    /**
     * Validation: store enforces unique internal_name.
     */
    public function test_store_enforces_unique_internal_name()
    {
        $existing = Glossary::factory()->create();
        $data = Glossary::factory()->make(['internal_name' => $existing->internal_name])->except(['id']);

        $response = $this->postJson(route('glossary.store'), $data);
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }
}

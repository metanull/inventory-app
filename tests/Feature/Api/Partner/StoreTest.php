<?php

namespace Tests\Feature\Api\Partner;

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

    public function test_store_allows_authenticated_users(): void
    {
        $data = [
            'name' => $this->faker->company(),
        ];

        $response = $this->postJson(route('partner.store'), $data);

        $response->assertCreated();
    }

    public function test_store_forbids_anonymous_access(): void
    {
        $data = [
            'name' => $this->faker->company(),
        ];

        $response = $this->postJson(route('partner.store'), $data);

        $response->assertUnauthorized();
    }

    public function test_store_creates_a_row(): void
    {
        $data = [
            'name' => $this->faker->company(),
        ];

        $response = $this->postJson(route('partner.store'), $data);

        $response->assertCreated();
        $this->assertDatabaseHas('partners', $data);
    }

    public function test_store_returns_created_on_success(): void
    {
        $data = [
            'name' => $this->faker->company(),
        ];

        $response = $this->postJson(route('partner.store'), $data);

        $response->assertCreated();
    }

    public function test_store_returns_unprocessable_entity_when_input_is_invalid(): void
    {
        $data = [
            'name' => '', // Invalid: empty name
        ];

        $response = $this->postJson(route('partner.store'), $data);

        $response->assertUnprocessable();
    }

    public function test_store_returns_the_expected_structure(): void
    {
        $data = [
            'name' => $this->faker->company(),
        ];

        $response = $this->postJson(route('partner.store'), $data);

        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_store_returns_the_expected_data(): void
    {
        $data = [
            'name' => $this->faker->company(),
        ];

        $response = $this->postJson(route('partner.store'), $data);

        $response->assertCreated();
        $response->assertJsonPath('data.name', $data['name']);
        $this->assertDatabaseHas('partners', $data);
    }

    public function test_store_validates_its_input(): void
    {
        $invalidData = [
            'name' => '', // Required field empty
        ];

        $response = $this->postJson(route('partner.store'), $invalidData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name']);
    }
}

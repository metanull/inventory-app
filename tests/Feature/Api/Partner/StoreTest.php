<?php

namespace Tests\Feature\Api\Partner;

use App\Models\Partner;
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
        // Create user with all data permissions for store operations
        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function test_store_allows_authenticated_users(): void
    {
        $data = Partner::factory()->make()->except('id');

        $response = $this->postJson(route('partner.store'), $data);

        $response->assertCreated();
    }

    public function test_store_creates_a_row(): void
    {
        $data = Partner::factory()->make()->except('id');

        $response = $this->postJson(route('partner.store'), $data);

        $response->assertCreated();
        $this->assertDatabaseHas('partners', $data);
    }

    public function test_store_returns_created_on_success(): void
    {
        $data = Partner::factory()->make()->except('id');

        $response = $this->postJson(route('partner.store'), $data);

        $response->assertCreated();
    }

    public function test_store_returns_unprocessable_entity_when_input_is_invalid(): void
    {
        $data = [
            'internal_name' => '', // Invalid: empty name
            // Missing 'type' field
        ];

        $response = $this->postJson(route('partner.store'), $data);

        $response->assertUnprocessable();
    }

    public function test_store_returns_the_expected_structure(): void
    {
        $data = Partner::factory()->make()->except('id');

        $response = $this->postJson(route('partner.store'), $data);

        $response->assertJsonStructure([
            'data' => [
                'id',
                'internal_name',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_store_returns_the_expected_data(): void
    {
        $data = Partner::factory()->make()->except('id');

        $response = $this->postJson(route('partner.store'), $data);

        $response->assertCreated();
        $response->assertJsonPath('data.internal_name', $data['internal_name']);
        $this->assertDatabaseHas('partners', $data);
    }

    public function test_store_validates_its_input(): void
    {
        $invalidData = [
            'internal_name' => '', // Required field empty
            // Missing 'type' field
        ];

        $response = $this->postJson(route('partner.store'), $invalidData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name', 'type']);
    }
}

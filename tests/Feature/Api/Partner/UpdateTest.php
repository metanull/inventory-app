<?php

namespace Tests\Feature\Api\Partner;

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
        $partner = Partner::factory()->create();
        $data = [
            'name' => $this->faker->company(),
        ];

        $response = $this->putJson(route('partner.update', $partner), $data);

        $response->assertOk();
    }

    public function test_update_updates_a_row(): void
    {
        $partner = Partner::factory()->create();
        $data = [
            'name' => $this->faker->company(),
        ];

        $response = $this->putJson(route('partner.update', $partner), $data);

        $response->assertOk();
        $this->assertDatabaseHas('partners', array_merge(['id' => $partner->id], $data));
    }

    public function test_update_returns_ok_on_success(): void
    {
        $partner = Partner::factory()->create();
        $data = [
            'name' => $this->faker->company(),
        ];

        $response = $this->putJson(route('partner.update', $partner), $data);

        $response->assertOk();
    }

    public function test_update_returns_not_found_when_record_does_not_exist(): void
    {
        $data = [
            'name' => $this->faker->company(),
        ];

        $response = $this->putJson(route('partner.update', 999), $data);

        $response->assertNotFound();
    }

    public function test_update_returns_unprocessable_entity_when_input_is_invalid(): void
    {
        $partner = Partner::factory()->create();
        $data = [
            'name' => '', // Invalid: empty name
        ];

        $response = $this->putJson(route('partner.update', $partner), $data);

        $response->assertUnprocessable();
    }

    public function test_update_returns_the_expected_structure(): void
    {
        $partner = Partner::factory()->create();
        $data = [
            'name' => $this->faker->company(),
        ];

        $response = $this->putJson(route('partner.update', $partner), $data);

        $response->assertJsonStructure([
            'data' => [
                'id',
                'name',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_update_returns_the_expected_data(): void
    {
        $partner = Partner::factory()->create();
        $data = [
            'name' => $this->faker->company(),
        ];

        $response = $this->putJson(route('partner.update', $partner), $data);

        $response->assertOk();
        $response->assertJsonPath('data.id', $partner->id);
        $response->assertJsonPath('data.name', $data['name']);
        $this->assertDatabaseHas('partners', array_merge(['id' => $partner->id], $data));
    }

    public function test_update_validates_its_input(): void
    {
        $partner = Partner::factory()->create();
        $invalidData = [
            'name' => '', // Required field empty
        ];

        $response = $this->putJson(route('partner.update', $partner), $invalidData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name']);
    }
}

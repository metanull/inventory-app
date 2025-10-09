<?php

namespace Tests\Feature\Api\Partner;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class UpdateTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->createDataUser();
        $this->actingAs($this->user);
    }

    public function test_update_allows_authenticated_users(): void
    {
        $partner = Partner::factory()->create();
        $data = Partner::factory()->make()->except(['id']);

        $response = $this->putJson(route('partner.update', $partner), $data);

        $response->assertOk();
    }

    public function test_update_updates_a_row(): void
    {
        $partner = Partner::factory()->create();
        $data = Partner::factory()->make()->except(['id']);

        $response = $this->putJson(route('partner.update', $partner), $data);

        $response->assertOk();
        $this->assertDatabaseHas('partners', array_merge(['id' => $partner->id], $data));
    }

    public function test_update_returns_ok_on_success(): void
    {
        $partner = Partner::factory()->create();
        $data = Partner::factory()->make()->except(['id']);

        $response = $this->putJson(route('partner.update', $partner), $data);

        $response->assertOk();
    }

    public function test_update_returns_not_found_when_record_does_not_exist(): void
    {
        $data = Partner::factory()->make()->except(['id']);

        $response = $this->putJson(route('partner.update', 999), $data);

        $response->assertNotFound();
    }

    public function test_update_returns_unprocessable_entity_when_input_is_invalid(): void
    {
        $partner = Partner::factory()->create();
        $invalidData = Partner::factory()->make()->except(['internal_name']); // Missing required field

        $response = $this->putJson(route('partner.update', $partner), $invalidData);

        $response->assertUnprocessable();
    }

    public function test_update_returns_the_expected_structure(): void
    {
        $partner = Partner::factory()->create();
        $data = Partner::factory()->make()->except(['id']);

        $response = $this->putJson(route('partner.update', $partner), $data);

        $response->assertJsonStructure([
            'data' => [
                'id',
                'internal_name',
                'created_at',
                'updated_at',
            ],
        ]);
    }

    public function test_update_returns_the_expected_data(): void
    {
        $partner = Partner::factory()->create();
        $data = Partner::factory()->make()->except(['id']);

        $response = $this->putJson(route('partner.update', $partner), $data);

        $response->assertOk();
        $response->assertJsonPath('data.id', $partner->id);
        $response->assertJsonPath('data.internal_name', $data['internal_name']);
        $this->assertDatabaseHas('partners', array_merge(['id' => $partner->id], $data));
    }

    public function test_update_validates_its_input(): void
    {
        $partner = Partner::factory()->create();
        $invalidData = Partner::factory()->make()->except(['internal_name']); // Missing required field

        $response = $this->putJson(route('partner.update', $partner), $invalidData);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }
}

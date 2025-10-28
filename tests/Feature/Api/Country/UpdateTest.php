<?php

namespace Tests\Feature\Api\Country;

use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class UpdateTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        // Create user with UPDATE_DATA permission for update operations
        $this->user = $this->createUserWithPermissions(['update data']);
        $this->actingAs($this->user);
    }

    /**
     * Response: Assert update returns ok on success.
     */
    public function test_update_returns_ok_on_success()
    {
        $country = Country::factory()->create();
        $data = ['internal_name' => 'Updated Name'];

        $response = $this->putJson(route('country.update', $country), $data);

        $response->assertOk();
    }

    /**
     * Response: Assert update returns not found when record does not exist.
     */
    public function test_update_returns_not_found_when_record_does_not_exist()
    {
        $data = ['internal_name' => 'Updated Name'];

        $response = $this->putJson(route('country.update', ['country' => 'non-existent-id']), $data);

        $response->assertNotFound();
    }

    /**
     * Response: Assert update returns unprocessable entity when input is invalid.
     */
    public function test_update_returns_unprocessable_entity_when_input_is_invalid()
    {
        $country = Country::factory()->create();

        $response = $this->putJson(route('country.update', $country), []);

        $response->assertUnprocessable();
    }

    /**
     * Validation: Assert update validates its input.
     */
    public function test_update_validates_its_input()
    {
        $country = Country::factory()->create();

        $response = $this->putJson(route('country.update', $country), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    /**
     * Authentication: Assert update allows authenticated users.
     */
    public function test_update_allows_authenticated_users()
    {
        $country = Country::factory()->create();
        $data = ['internal_name' => 'Updated Name'];

        $response = $this->putJson(route('country.update', $country), $data);

        $response->assertOk();
    }

    /**
     * Process: Assert update updates a row.
     */
    public function test_update_updates_a_row()
    {
        $country = Country::factory()->create();
        $data = ['internal_name' => 'Updated Name'];

        $this->putJson(route('country.update', $country), $data);

        $this->assertDatabaseHas('countries', [
            'id' => $country->id,
            'internal_name' => 'Updated Name',
        ]);
    }
}

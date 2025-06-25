<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Country;
use App\Models\Item;
use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CountryTest extends TestCase
{

    use RefreshDatabase, WithFaker;


    /**
     * Factory: Assert the Country factory creates the expected data.
     */
    public function test_factory()
    {
        $country = Country::factory()->create();

        $this->assertInstanceOf(Country::class, $country);
        $this->assertNotEmpty($country->id);
        $this->assertNotEmpty($country->internal_name);
    }

    /**
     * Response: Assert index returns ok on success.
     */
    public function test_api_response_index_returns_ok_on_success()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Country::factory()->count(2)->create();

        $response = $this->getJson(route('country.index'));

        $response->assertOk();
    }

    /**
     * Response: Assert show returns ok on success.
     */
    public function test_api_response_show_returns_ok_on_success()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $country = Country::factory()->create();

        $response = $this->getJson(route('country.show', $country));

        $response->assertOk();
    }

    /**
     * Response: Assert show returns not found when record does not exist.
     */
    public function test_api_response_show_returns_not_found_when_record_does_not_exist()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson(route('country.show', ['country' => 'non-existent-id']));

        $response->assertNotFound();
    }

    /**
     * Response: Assert store returns created on success.
     */
    public function test_api_response_store_returns_created_on_success()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $data = Country::factory()->make()->toArray();

        $response = $this->postJson(route('country.store'), $data);

        $response->assertCreated();
    }

    /**
     * Response: Assert store returns unprocessable entity when input is invalid.
     */
    public function test_api_response_store_returns_unprocessable_entity_when_input_is_invalid()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson(route('country.store'), []);

        $response->assertUnprocessable();
    }

    /**
     * Response: Assert update returns ok on success.
     */
    public function test_api_response_update_returns_ok_on_success()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $country = Country::factory()->create();
        $data = ['internal_name' => 'Updated Name'];

        $response = $this->putJson(route('country.update', $country), $data);

        $response->assertOk();
    }

    /**
     * Response: Assert update returns not found when record does not exist.
     */
    public function test_api_response_update_returns_not_found_when_record_does_not_exist()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $data = ['internal_name' => 'Updated Name'];

        $response = $this->putJson(route('country.update', ['country' => 'non-existent-id']), $data);

        $response->assertNotFound();
    }

    /**
     * Response: Assert update returns unprocessable entity when input is invalid.
     */
    public function test_api_response_update_returns_unprocessable_entity_when_input_is_invalid()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $country = Country::factory()->create();

        $response = $this->putJson(route('country.update', $country), []);

        $response->assertUnprocessable();
    }

    /**
     * Response: Assert destroy returns no content on success.
     */
    public function test_api_response_destroy_returns_no_content_on_success()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $country = Country::factory()->create();

        $response = $this->deleteJson(route('country.destroy', $country));

        $response->assertNoContent();
    }

    /**
     * Response: Assert destroy returns not found when record does not exist.
     */
    public function test_api_response_destroy_returns_not_found_when_record_does_not_exist()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->deleteJson(route('country.destroy', ['country' => 'non-existent-id']));

        $response->assertNotFound();
    }

    /**
     * Response: Assert index returns the expected structure.
     */
    public function test_api_response_index_returns_the_expected_structure()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Country::factory()->count(2)->create();

        $response = $this->getJson(route('country.index'));

        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'internal_name', 'backward_compatibility'],
            ],
        ]);
    }

    /**
     * Response: Assert show returns the expected structure.
     */
    public function test_api_response_show_returns_the_expected_structure()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $country = Country::factory()->create();

        $response = $this->getJson(route('country.show', $country));

        $response->assertJsonStructure([
            'data' => ['id', 'internal_name', 'backward_compatibility'],
        ]);
    }

    /**
     * Validation: Assert store validates its input.
     */
    public function test_api_validation_store_validates_its_input()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson(route('country.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id', 'internal_name']);
    }

    /**
     * Validation: Assert update validates its input.
     */
    public function test_api_validation_update_validates_its_input()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $country = Country::factory()->create();

        $response = $this->putJson(route('country.update', $country), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    /**
     * Authentication: Assert index allows authenticated users.
     */
    public function test_api_authentication_index_allows_authenticated_users()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson(route('country.index'));

        $response->assertOk();
    }

    /**
     * Authentication: Assert index forbids anonymous access.
     */
    public function test_api_authentication_index_forbids_anonymous_access()
    {
        $response = $this->getJson(route('country.index'));

        $response->assertUnauthorized();
    }

    /**
     * Authentication: Assert show allows authenticated users.
     */
    public function test_api_authentication_show_allows_authenticated_users()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $country = Country::factory()->create();

        $response = $this->getJson(route('country.show', $country));

        $response->assertOk();
    }

    /**
     * Authentication: Assert show forbids anonymous access.
     */
    public function test_api_authentication_show_forbids_anonymous_access()
    {
        $country = Country::factory()->create();

        $response = $this->getJson(route('country.show', $country));

        $response->assertUnauthorized();
    }

    /**
     * Authentication: Assert store allows authenticated users.
     */
    public function test_api_authentication_store_allows_authenticated_users()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $data = Country::factory()->make()->toArray();

        $response = $this->postJson(route('country.store'), $data);

        $response->assertCreated();
    }

    /**
     * Authentication: Assert store forbids anonymous access.
     */
    public function test_api_authentication_store_forbids_anonymous_access()
    {
        $data = Country::factory()->make()->toArray();

        $response = $this->postJson(route('country.store'), $data);

        $response->assertUnauthorized();
    }

    /**
     * Authentication: Assert update allows authenticated users.
     */
    public function test_api_authentication_update_allows_authenticated_users()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $country = Country::factory()->create();
        $data = ['internal_name' => 'Updated Name'];

        $response = $this->putJson(route('country.update', $country), $data);

        $response->assertOk();
    }

    /**
     * Authentication: Assert update forbids anonymous access.
     */
    public function test_api_authentication_update_forbids_anonymous_access()
    {
        $country = Country::factory()->create();
        $data = ['internal_name' => 'Updated Name'];

        $response = $this->putJson(route('country.update', $country), $data);

        $response->assertUnauthorized();
    }

    /**
     * Authentication: Assert destroy allows authenticated users.
     */
    public function test_api_authentication_destroy_allows_authenticated_users()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $country = Country::factory()->create();

        $response = $this->deleteJson(route('country.destroy', $country));

        $response->assertNoContent();
    }

    /**
     * Authentication: Assert destroy forbids anonymous access.
     */
    public function test_api_authentication_destroy_forbids_anonymous_access()
    {
        $country = Country::factory()->create();

        $response = $this->deleteJson(route('country.destroy', $country));

        $response->assertUnauthorized();
    }

    /**
     * Process: Assert index returns all rows.
     */
    public function test_api_process_index_returns_all_rows()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        Country::factory()->count(3)->create();

        $response = $this->getJson(route('country.index'));

        $response->assertJsonCount(3, 'data');
    }

    /**
     * Process: Assert show returns one row.
     */
    public function test_api_process_show_returns_one_row()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $country = Country::factory()->create();

        $response = $this->getJson(route('country.show', $country));

        $response->assertJsonPath('data.id', $country->id);
    }

    /**
     * Process: Assert store creates a row.
     */
    public function test_api_process_store_creates_a_row()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $data = Country::factory()->make()->toArray();

        $this->postJson(route('country.store'), $data);

        $this->assertDatabaseHas('countries', ['id' => $data['id']]);
    }

    /**
     * Process: Assert update updates a row.
     */
    public function test_api_process_update_updates_a_row()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $country = Country::factory()->create();
        $data = ['internal_name' => 'Updated Name'];

        $this->putJson(route('country.update', $country), $data);

        $this->assertDatabaseHas('countries', [
            'id' => $country->id,
            'internal_name' => 'Updated Name',
        ]);
    }

    /**
     * Process: Assert destroy deletes a row.
     */
    public function test_api_process_destroy_deletes_a_row()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $country = Country::factory()->create();

        $this->deleteJson(route('country.destroy', $country));

        $this->assertDatabaseMissing('countries', ['id' => $country->id]);
    }

    /**
     * Helper: Authenticate as a user.
     */
    protected function actingAsUser()
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);
    }
}
<?php

namespace Tests\Feature\Api\Country;

use App\Models\User;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowTest extends TestCase
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
     * Response: Assert show returns ok on success.
     */
    public function test_api_response_show_returns_ok_on_success()
    {
        $country = Country::factory()->create();

        $response = $this->getJson(route('country.show', $country));

        $response->assertOk();
    }

    /**
     * Response: Assert show returns not found when record does not exist.
     */
    public function test_api_response_show_returns_not_found_when_record_does_not_exist()
    {
        $response = $this->getJson(route('country.show', ['country' => 'non-existent-id']));

        $response->assertNotFound();
    }

    /**
     * Response: Assert show returns the expected structure.
     */
    public function test_api_response_show_returns_the_expected_structure()
    {
        $country = Country::factory()->create();

        $response = $this->getJson(route('country.show', $country));

        $response->assertJsonStructure([
            'data' => ['id', 'internal_name', 'backward_compatibility'],
        ]);
    }

    /**
     * Authentication: Assert show allows authenticated users.
     */
    public function test_api_authentication_show_allows_authenticated_users()
    {
        $country = Country::factory()->create();

        $response = $this->getJson(route('country.show', $country));

        $response->assertOk();
    }

    /**
     * Process: Assert show returns one row.
     */
    public function test_api_process_show_returns_one_row()
    {
        $country = Country::factory()->create();

        $response = $this->getJson(route('country.show', $country));

        $response->assertJsonPath('data.id', $country->id);
    }
}
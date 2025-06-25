<?php

namespace Tests\Feature\Api\Country;

use App\Models\User;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class IndexTest extends TestCase
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
     * Response: Assert index returns ok on success.
     */
    public function test_index_returns_ok_on_success()
    {
        Country::factory()->count(2)->create();

        $response = $this->getJson(route('country.index'));

        $response->assertOk();
    }

    /**
     * Response: Assert index returns the expected structure.
     */
    public function test_index_returns_the_expected_structure()
    {
        Country::factory()->count(2)->create();

        $response = $this->getJson(route('country.index'));

        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'internal_name', 'backward_compatibility'],
            ],
        ]);
    }

    /**
     * Authentication: Assert index allows authenticated users.
     */
    public function test_index_allows_authenticated_users()
    {
        $response = $this->getJson(route('country.index'));

        $response->assertOk();
    }

    /**
     * Process: Assert index returns all rows.
     */
    public function test_index_returns_all_rows()
    {
        Country::factory()->count(3)->create();

        $response = $this->getJson(route('country.index'));

        $response->assertJsonCount(3, 'data');
    }
}
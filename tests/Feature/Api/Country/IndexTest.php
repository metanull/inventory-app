<?php

namespace Tests\Feature\Api\Country;

use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class IndexTest extends TestCase
{
    use CreatesUsersWithPermissions, RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        // Create user with VIEW_DATA permission for read operations
        $this->user = $this->createVisitorUser();
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
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'],
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

        $response->assertJsonPath('meta.total', 3);
    }

    public function test_index_accepts_pagination_parameters(): void
    {
        Country::factory(5)->create();

        $response = $this->getJson(route('country.index', ['per_page' => 2, 'page' => 1]));

        $response->assertOk()
            ->assertJsonStructure([
                'meta' => [
                    'per_page',
                    'current_page',
                    'total',
                    'last_page',
                ],
                'data' => [],
            ])
            ->assertJsonPath('meta.current_page', 1);

        $this->assertCount(2, $response->json('data'));
        $this->assertIsInt($response->json('meta.per_page'));
        $this->assertGreaterThan(0, $response->json('meta.per_page'));
    }

    public function test_index_validates_pagination_parameters(): void
    {
        $response = $this->getJson(route('country.index', ['per_page' => 0]));
        $response->assertUnprocessable();

        $response = $this->getJson(route('country.index', ['per_page' => 101]));
        $response->assertUnprocessable();

        $response = $this->getJson(route('country.index', ['page' => 0]));
        $response->assertUnprocessable();
    }
}

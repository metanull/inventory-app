<?php

namespace Tests\Feature\Api\Info;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HealthTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    // #region HEALTH ENDPOINT TESTS

    /**
     * Success: Assert authenticated users can access health endpoint.
     */
    public function test_authenticated_user_can_access_health()
    {
        $response = $this->getJson(route('info.health'));

        $response->assertOk();
    }

    /**
     * Structure: Assert health response structure for authenticated users.
     */
    public function test_authenticated_health_response_structure()
    {
        $response = $this->getJson(route('info.health'));

        $response->assertOk()
            ->assertJsonStructure([
                'status',
                'checks' => [
                    'database' => [
                        'status',
                        'message',
                    ],
                    'cache' => [
                        'status',
                        'message',
                    ],
                ],
                'timestamp',
            ]);
    }

    /**
     * Content: Assert health endpoint returns valid status values.
     */
    public function test_health_returns_valid_status_values()
    {
        $response = $this->getJson(route('info.health'));

        $response->assertOk();

        $data = $response->json();

        // Overall status should be valid
        $this->assertContains($data['status'], ['healthy', 'unhealthy']);

        // Individual health checks should be valid
        $this->assertContains($data['checks']['database']['status'], ['healthy', 'unhealthy']);
        $this->assertContains($data['checks']['cache']['status'], ['healthy', 'unhealthy']);
    }

    // #endregion

    // #region INFO ENDPOINT TESTS

    /**
     * Success: Assert authenticated users can access info endpoint.
     */
    public function test_authenticated_user_can_access_info()
    {
        $response = $this->getJson(route('info.index'));

        $response->assertOk();
    }

    /**
     * Structure: Assert complete info response structure for authenticated users.
     */
    public function test_authenticated_info_response_structure()
    {
        $response = $this->getJson(route('info.index'));

        $response->assertOk()
            ->assertJsonStructure([
                'application' => [
                    'name',
                    'version',
                    'environment',
                ],
                'health' => [
                    'status',
                    'checks' => [
                        'database' => [
                            'status',
                            'message',
                        ],
                        'cache' => [
                            'status',
                            'message',
                        ],
                    ],
                ],
                'timestamp',
            ]);
    }

    // #endregion
}

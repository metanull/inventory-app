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

    /**
     * Content: Assert health checks include meaningful messages.
     */
    public function test_health_checks_include_meaningful_messages()
    {
        $response = $this->getJson(route('info.health'));

        $response->assertOk();

        $data = $response->json();

        // Database check should have a message
        $this->assertIsString($data['checks']['database']['message']);
        $this->assertNotEmpty($data['checks']['database']['message']);

        // Cache check should have a message
        $this->assertIsString($data['checks']['cache']['message']);
        $this->assertNotEmpty($data['checks']['cache']['message']);
    }

    /**
     * Content: Assert timestamp is present and valid.
     */
    public function test_health_timestamp_is_valid()
    {
        $response = $this->getJson(route('info.health'));

        $response->assertOk();

        $data = $response->json();
        $this->assertIsString($data['timestamp']);

        // Validate it's a proper RFC 3339/ISO 8601 timestamp (Laravel's toISOString format)
        $timestamp = \DateTime::createFromFormat('Y-m-d\TH:i:s.u\Z', $data['timestamp']);
        if (! $timestamp) {
            // Fallback for timestamps without microseconds
            $timestamp = \DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $data['timestamp']);
        }
        $this->assertInstanceOf(\DateTime::class, $timestamp);
    }

    /**
     * Integration: Assert healthy database shows healthy status.
     */
    public function test_healthy_database_shows_healthy_status()
    {
        // Since we're using the test database and it's working, this should be healthy
        $response = $this->getJson(route('info.health'));

        $response->assertOk();

        $data = $response->json();
        $this->assertEquals('healthy', $data['checks']['database']['status']);
        $this->assertStringContainsString('successful', $data['checks']['database']['message']);
    }

    /**
     * Integration: Assert cache operations work correctly.
     */
    public function test_cache_operations_work_correctly()
    {
        // Since we're using the test cache and it's working, this should be healthy
        $response = $this->getJson(route('info.health'));

        $response->assertOk();

        $data = $response->json();
        $this->assertEquals('healthy', $data['checks']['cache']['status']);
        $this->assertStringContainsString('successful', $data['checks']['cache']['message']);
    }
}

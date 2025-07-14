<?php

namespace Tests\Feature\Api\Info;

use App\Models\User;
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

    /**
     * Content: Assert info returns correct application information.
     */
    public function test_info_returns_correct_application_information()
    {
        $response = $this->getJson(route('info.index'));

        $response->assertOk()
            ->assertJsonPath('application.name', config('app.name'))
            ->assertJsonPath('application.environment', config('app.env'));

        $data = $response->json();
        $this->assertIsString($data['application']['version']);
        $this->assertNotEmpty($data['application']['version']);
    }

    /**
     * Content: Assert health checks return expected results.
     */
    public function test_health_checks_return_expected_results()
    {
        $response = $this->getJson(route('info.index'));

        $response->assertOk();

        $data = $response->json();

        // Overall status should be valid
        $this->assertContains($data['health']['status'], ['healthy', 'unhealthy']);

        // Database check should be present and valid
        $this->assertArrayHasKey('database', $data['health']['checks']);
        $this->assertContains($data['health']['checks']['database']['status'], ['healthy', 'unhealthy']);
        $this->assertIsString($data['health']['checks']['database']['message']);

        // Cache check should be present and valid
        $this->assertArrayHasKey('cache', $data['health']['checks']);
        $this->assertContains($data['health']['checks']['cache']['status'], ['healthy', 'unhealthy']);
        $this->assertIsString($data['health']['checks']['cache']['message']);
    }

    /**
     * Content: Assert timestamp is valid ISO 8601 format.
     */
    public function test_timestamp_is_valid_iso_format()
    {
        $response = $this->getJson(route('info.index'));

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
}

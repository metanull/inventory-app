<?php

namespace Tests\Feature\Api\Info;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Authentication: Assert info allows anonymous access.
     */
    public function test_info_allows_anonymous_access()
    {
        $response = $this->getJson(route('info.index'));

        $response->assertOk();
    }

    /**
     * Authentication: Assert health allows anonymous access.
     */
    public function test_health_allows_anonymous_access()
    {
        $response = $this->getJson(route('info.health'));

        $response->assertOk();
    }

    /**
     * Authentication: Assert version allows anonymous access.
     */
    public function test_version_allows_anonymous_access()
    {
        $response = $this->getJson(route('info.version'));

        $response->assertOk();
    }

    /**
     * Structure: Assert info response structure.
     */
    public function test_info_response_structure()
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
     * Structure: Assert health response structure.
     */
    public function test_health_response_structure()
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
     * Structure: Assert version response structure.
     */
    public function test_version_response_structure()
    {
        $response = $this->getJson(route('info.version'));

        $response->assertOk()
            ->assertJsonStructure([
                'repository',
                'build_timestamp' => [
                    'value',
                    'DisplayHint',
                    'DateTime',
                ],
                'repository_url',
                'api_client_version',
                'app_version',
                'commit_sha',
            ]);
    }

    /**
     * Content: Assert info contains expected application data.
     */
    public function test_info_contains_application_data()
    {
        $response = $this->getJson(route('info.index'));

        $response->assertOk()
            ->assertJsonPath('application.name', config('app.name'))
            ->assertJsonPath('application.environment', config('app.env'));
    }

    /**
     * Content: Assert health status values are valid.
     */
    public function test_health_status_values_are_valid()
    {
        $response = $this->getJson(route('info.health'));

        $response->assertOk();

        $data = $response->json();
        $this->assertContains($data['status'], ['healthy', 'unhealthy']);
        $this->assertContains($data['checks']['database']['status'], ['healthy', 'unhealthy']);
        $this->assertContains($data['checks']['cache']['status'], ['healthy', 'unhealthy']);
    }

    /**
     * Content: Assert version endpoint returns version string.
     */
    public function test_version_returns_version_string()
    {
        $response = $this->getJson(route('info.version'));

        $response->assertOk();

        $data = $response->json();
        $this->assertIsString($data['app_version']);
        $this->assertNotEmpty($data['app_version']);
    }
}

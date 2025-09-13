<?php

namespace Tests\Feature\Api\Info;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class VersionTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    protected function tearDown(): void
    {
        // Clean up any VERSION file created during tests
        $versionPath = base_path('VERSION');
        if (file_exists($versionPath)) {
            unlink($versionPath);
        }

        parent::tearDown();
    }

    private function createMockVersionFile(?array $data = null): void
    {
        $defaultData = [
            'repository' => 'metanull/inventory-app',
            'build_timestamp' => [
                'value' => '/Date(1757794373908)/',
                'DisplayHint' => 2,
                'DateTime' => 'zaterdag 13 september 2025 22:12:53',
            ],
            'repository_url' => 'https://github.com/metanull/inventory-app',
            'api_client_version' => '1.1.24-dev.0912.1709',
            'app_version' => '4.1.7',
            'commit_sha' => '1e3b8e37cab51bf27faa916eec9e66b2beadb931',
        ];

        $versionData = $data ?? $defaultData;
        File::put(base_path('VERSION'), json_encode($versionData));
    }

    /**
     * Success: Assert authenticated users can access version endpoint.
     */
    public function test_authenticated_user_can_access_version()
    {
        $response = $this->getJson(route('info.version'));

        $response->assertOk();
    }

    /**
     * Structure: Assert version response structure when VERSION file exists (CI/CD scenario).
     */
    public function test_authenticated_version_response_structure_with_version_file()
    {
        $this->createMockVersionFile();

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
     * Structure: Assert version response structure when no VERSION file exists (fallback scenario).
     */
    public function test_authenticated_version_response_structure_fallback()
    {
        // Ensure no VERSION file exists
        $versionPath = base_path('VERSION');
        if (file_exists($versionPath)) {
            unlink($versionPath);
        }

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
     * Content: Assert version endpoint returns repository information when VERSION file exists.
     */
    public function test_version_returns_repository_information()
    {
        $this->createMockVersionFile();

        $response = $this->getJson(route('info.version'));

        $response->assertOk()
            ->assertJsonPath('repository', 'metanull/inventory-app')
            ->assertJsonPath('repository_url', 'https://github.com/metanull/inventory-app');
    }

    /**
     * Content: Assert version endpoint returns valid app version string.
     */
    public function test_version_returns_valid_app_version_string()
    {
        $this->createMockVersionFile();

        $response = $this->getJson(route('info.version'));

        $response->assertOk();

        $data = $response->json();
        $this->assertIsString($data['app_version']);
        $this->assertNotEmpty($data['app_version']);

        // Version should not be null or empty
        $this->assertNotNull($data['app_version']);
        $this->assertGreaterThan(0, strlen(trim($data['app_version'])));
    }

    /**
     * Content: Assert build timestamp is present and has expected structure.
     */
    public function test_version_build_timestamp_structure()
    {
        $this->createMockVersionFile();

        $response = $this->getJson(route('info.version'));

        $response->assertOk();

        $data = $response->json();
        $this->assertIsArray($data['build_timestamp']);
        $this->assertArrayHasKey('DateTime', $data['build_timestamp']);
        $this->assertArrayHasKey('value', $data['build_timestamp']);
        $this->assertArrayHasKey('DisplayHint', $data['build_timestamp']);

        $this->assertIsString($data['build_timestamp']['DateTime']);
        $this->assertNotEmpty($data['build_timestamp']['DateTime']);
    }

    /**
     * Content: Assert app version format is reasonable.
     */
    public function test_app_version_format_is_reasonable()
    {
        $this->createMockVersionFile();

        $response = $this->getJson(route('info.version'));

        $response->assertOk();

        $data = $response->json();
        $version = $data['app_version'];

        // Version should be either a semantic version, git hash, or development version
        $this->assertTrue(
            preg_match('/^\d+\.\d+\.\d+/', $version) || // Semantic versioning
            preg_match('/^[a-f0-9]{8}$/', $version) || // Git hash (8 chars)
            preg_match('/^\d+\.\d+\.\d+-dev$/', $version) || // Development version
            $version === '1.0.0-dev', // Default fallback
            "Version '{$version}' does not match expected format"
        );
    }

    /**
     * Integration: Assert version is consistent across calls.
     */
    public function test_version_is_consistent_across_calls()
    {
        $this->createMockVersionFile();

        $response1 = $this->getJson(route('info.version'));
        $response2 = $this->getJson(route('info.version'));

        $response1->assertOk();
        $response2->assertOk();

        $data1 = $response1->json();
        $data2 = $response2->json();

        $this->assertEquals($data1['app_version'], $data2['app_version']);
        $this->assertEquals($data1['repository'], $data2['repository']);
        $this->assertEquals($data1['commit_sha'], $data2['commit_sha']);
    }

    /**
     * Content: Assert API client version is returned when VERSION file exists.
     */
    public function test_version_returns_api_client_version()
    {
        $this->createMockVersionFile();

        $response = $this->getJson(route('info.version'));

        $response->assertOk();

        $data = $response->json();
        $this->assertArrayHasKey('api_client_version', $data);
        $this->assertIsString($data['api_client_version']);
        $this->assertNotEmpty($data['api_client_version']);
    }

    /**
     * Fallback: Assert fallback behavior when VERSION file is corrupted.
     */
    public function test_version_fallback_on_corrupted_file()
    {
        // Create a corrupted VERSION file
        File::put(base_path('VERSION'), '{"invalid": json}');

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
}

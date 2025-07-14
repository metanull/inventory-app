<?php

namespace Tests\Feature\Api\Info;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
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

    /**
     * Success: Assert authenticated users can access version endpoint.
     */
    public function test_authenticated_user_can_access_version()
    {
        $response = $this->getJson(route('info.version'));

        $response->assertOk();
    }

    /**
     * Structure: Assert version response structure for authenticated users.
     */
    public function test_authenticated_version_response_structure()
    {
        $response = $this->getJson(route('info.version'));

        $response->assertOk()
            ->assertJsonStructure([
                'version',
                'name',
                'timestamp',
            ]);
    }

    /**
     * Content: Assert version endpoint returns correct application name.
     */
    public function test_version_returns_correct_application_name()
    {
        $response = $this->getJson(route('info.version'));

        $response->assertOk()
            ->assertJsonPath('name', config('app.name'));
    }

    /**
     * Content: Assert version endpoint returns valid version string.
     */
    public function test_version_returns_valid_version_string()
    {
        $response = $this->getJson(route('info.version'));

        $response->assertOk();

        $data = $response->json();
        $this->assertIsString($data['version']);
        $this->assertNotEmpty($data['version']);

        // Version should not be null or empty
        $this->assertNotNull($data['version']);
        $this->assertGreaterThan(0, strlen(trim($data['version'])));
    }

    /**
     * Content: Assert timestamp is present and valid.
     */
    public function test_version_timestamp_is_valid()
    {
        $response = $this->getJson(route('info.version'));

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
     * Content: Assert version format is reasonable.
     */
    public function test_version_format_is_reasonable()
    {
        $response = $this->getJson(route('info.version'));

        $response->assertOk();

        $data = $response->json();
        $version = $data['version'];

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
        $response1 = $this->getJson(route('info.version'));
        $response2 = $this->getJson(route('info.version'));

        $response1->assertOk();
        $response2->assertOk();

        $data1 = $response1->json();
        $data2 = $response2->json();

        $this->assertEquals($data1['version'], $data2['version']);
        $this->assertEquals($data1['name'], $data2['name']);
    }
}

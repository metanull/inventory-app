<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiDocsAccessTest extends TestCase
{
    /*
        This test ensures the route is publicly accessible
        */
    public function test_api_json_is_public(): void
    {
        $response = $this->get(route('api.documentation'));

        $response->assertOk();
    }

    public function test_api_json_is_valid(): void
    {
        $response = $this->get(route('api.documentation'));

        $response->assertHeader('Content-Type', 'application/json');

        // Check that Cache-Control header contains both values (order may vary)
        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('max-age=3600', $cacheControl);

        // Verify OpenAPI 3.1.0 structure
        $responseData = $response->json();
        $this->assertArrayHasKey('openapi', $responseData);
        $this->assertEquals('3.1.0', $responseData['openapi']);
        $this->assertArrayHasKey('info', $responseData);
        $this->assertArrayHasKey('title', $responseData['info']);
        $this->assertArrayHasKey('version', $responseData['info']);
        $this->assertArrayHasKey('paths', $responseData);
        $this->assertIsArray($responseData['paths']);
    }

    /**
     * Test API docs are accessible in local environment
     */
    public function test_api_docs_accessible_in_local_environment(): void
    {
        // Ensure we're in local environment for this test
        app()->detectEnvironment(function () {
            return 'local';
        });

        $response = $this->get(route('scramble.docs.ui'));
        $response->assertOk();

        $response = $this->get(route('scramble.docs.document'));
        $response->assertOk();
    }

    /**
     * Test API docs are returning expected content-type
     */
    public function test_api_docs_return_expected_content_type(): void
    {
        // Ensure we're in local environment for this test
        app()->detectEnvironment(function () {
            return 'local';
        });

        $response = $this->get(route('scramble.docs.ui'));
        $response->assertHeader('content-type', 'text/html; charset=UTF-8');

        $response = $this->get(route('scramble.docs.document'));
        $response->assertHeader('content-type', 'application/json');
    }

    /**
     * Test API docs are accessible in testing environment
     */
    public function test_api_docs_accessible_in_testing_environment(): void
    {
        // Ensure we're in testing environment for this test
        app()->detectEnvironment(function () {
            return 'testing';
        });

        $response = $this->get(route('scramble.docs.ui'));
        $response->assertOk();

        $response = $this->get(route('scramble.docs.document'));
        $response->assertOk();
    }

    /**
     * Test API docs are blocked in production environment by default
     */
    public function test_api_docs_blocked_in_production_by_default(): void
    {
        // Set environment to production
        app()->detectEnvironment(function () {
            return 'production';
        });

        // Ensure API_DOCS_ENABLED is not set or false
        config(['app.env' => 'production']);
        putenv('API_DOCS_ENABLED=false');

        $response = $this->get(route('scramble.docs.ui'));
        $response->assertStatus(403);

        $response = $this->get(route('scramble.docs.document'));
        $response->assertStatus(403);
    }

    /**
     * Test API docs are accessible in production when explicitly enabled
     */
    public function test_api_docs_accessible_in_production_when_enabled(): void
    {
        // Set environment to production
        app()->detectEnvironment(function () {
            return 'production';
        });
        config(['app.env' => 'production']);

        // Enable API docs via environment variable
        putenv('API_DOCS_ENABLED=true');

        $response = $this->get(route('scramble.docs.ui'));
        $response->assertOk();

        $response = $this->get(route('scramble.docs.document'));
        $response->assertOk();
    }

    /**
     * Test API docs respect boolean-like values for API_DOCS_ENABLED
     */
    public function test_api_docs_respect_boolean_like_values(): void
    {
        // Set environment to production
        app()->detectEnvironment(function () {
            return 'production';
        });
        config(['app.env' => 'production']);

        // Test with string "1"
        putenv('API_DOCS_ENABLED=1');
        $response = $this->get(route('scramble.docs.ui'));
        $response->assertOk();

        // Test with string "0"
        putenv('API_DOCS_ENABLED=0');
        $response = $this->get(route('scramble.docs.ui'));
        $response->assertStatus(403);

        // Test with string "false"
        putenv('API_DOCS_ENABLED=false');
        $response = $this->get(route('scramble.docs.ui'));
        $response->assertStatus(403);

        // Clean up
        putenv('API_DOCS_ENABLED');
    }

    protected function tearDown(): void
    {
        // Clean up environment variables
        putenv('API_DOCS_ENABLED');
        parent::tearDown();
    }
}

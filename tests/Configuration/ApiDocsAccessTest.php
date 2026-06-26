<?php

namespace Tests\Configuration;

use Tests\TestCase;

class ApiDocsAccessTest extends TestCase
{
    public function test_api_json_is_valid(): void
    {
        $response = $this->get(route('api.documentation'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');

        // Check that Cache-Control header contains both values (order may vary)
        // $cacheControl = $response->headers->get('Cache-Control');
        // $this->assertStringContainsString('public', $cacheControl);
        // $this->assertStringContainsString('max-age=3600', $cacheControl);

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
        config(['app.env' => 'local']);

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
        config(['app.env' => 'local']);

        $response = $this->get(route('scramble.docs.ui'));
        $response->assertHeader('content-type', 'text/html; charset=utf-8');

        $response = $this->get(route('scramble.docs.document'));
        $response->assertHeader('content-type', 'application/json');
    }

    /**
     * Test API docs are accessible in testing environment
     */
    public function test_api_docs_accessible_in_testing_environment(): void
    {
        config(['app.env' => 'testing']);

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
        config(['app.env' => 'production', 'scramble.enabled' => false]);

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
        config(['app.env' => 'production', 'scramble.enabled' => true]);

        $response = $this->get(route('scramble.docs.ui'));
        $response->assertOk();

        $response = $this->get(route('scramble.docs.document'));
        $response->assertOk();
    }

    /**
     * Test API docs respect the scramble.enabled config flag
     */
    public function test_api_docs_respect_boolean_like_values(): void
    {
        config(['app.env' => 'production']);

        // Enabled
        config(['scramble.enabled' => true]);
        $response = $this->get(route('scramble.docs.ui'));
        $response->assertOk();

        // Disabled
        config(['scramble.enabled' => false]);
        $response = $this->get(route('scramble.docs.ui'));
        $response->assertStatus(403);
    }
}

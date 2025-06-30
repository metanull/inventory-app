<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiDocumentationTest extends TestCase
{
    public function test_api_json_route_returns_openapi_documentation(): void
    {
        $response = $this->get('/api.json');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');

        // Check that Cache-Control header contains both values (order may vary)
        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('max-age=3600', $cacheControl);

        $responseData = $response->json();
        $this->assertArrayHasKey('openapi', $responseData);
        $this->assertArrayHasKey('info', $responseData);
        $this->assertArrayHasKey('paths', $responseData);
        $this->assertEquals('3.1.0', $responseData['openapi']);
    }

    public function test_api_json_route_generates_valid_openapi_structure(): void
    {
        $response = $this->get('/api.json');

        $response->assertOk();
        $responseData = $response->json();

        // Verify OpenAPI 3.1.0 structure
        $this->assertArrayHasKey('openapi', $responseData);
        $this->assertArrayHasKey('info', $responseData);
        $this->assertArrayHasKey('paths', $responseData);

        // Verify info object structure
        $this->assertArrayHasKey('title', $responseData['info']);
        $this->assertArrayHasKey('version', $responseData['info']);

        // Verify paths is an object/array
        $this->assertIsArray($responseData['paths']);
    }

    public function test_api_json_route_is_accessible_without_authentication(): void
    {
        // This test ensures the route is publicly accessible
        $response = $this->get('/api.json');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/json');
    }
}

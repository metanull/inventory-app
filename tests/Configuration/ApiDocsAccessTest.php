<?php

namespace Tests\Feature;

use Tests\TestCase;

class ApiDocsAccessTest extends TestCase
{
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
        $response->assertStatus(200);

        $response = $this->get(route('scramble.docs.document'));
        $response->assertStatus(200);

        $response = $this->get(route('api.documentation'));
        $response->assertStatus(200);
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
        $response->assertStatus(200);

        $response = $this->get(route('scramble.docs.document'));
        $response->assertStatus(200);

        $response = $this->get(route('api.documentation'));
        $response->assertStatus(200);
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

        // api.json route doesn't use the middleware, so it should still work
        $response = $this->get(route('api.documentation'));
        $response->assertStatus(200);
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
        $response->assertStatus(200);

        $response = $this->get(route('scramble.docs.document'));
        $response->assertStatus(200);

        $response = $this->get(route('api.documentation'));
        $response->assertStatus(200);
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
        $response->assertStatus(200);

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

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BasicRoutingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test root route redirects to /web
     */
    public function test_root_redirects_to_web(): void
    {
        $response = $this->get(route('root'));

        $response->assertStatus(302);
        $response->assertRedirect('/web');
    }

    /**
     * Test /web route returns OK with HTML (Jetstream welcome page)
     */
    public function test_web_welcome_returns_html(): void
    {
        $response = $this->get(route('web.welcome'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/html; charset=UTF-8');

        $response->assertViewIs('welcome');
    }

    /**
     * Test /api returns OK (info endpoint)
     */
    public function test_api_info_returns_ok(): void
    {
        $response = $this->get(route('info.index'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/json');
    }

    /**
     * Test /api/health returns OK with JSON
     */
    public function test_api_health_returns_json(): void
    {
        $response = $this->get(route('info.health'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/json');

        // Check for typical health response structure
        $response->assertJsonStructure([
            'status',
            'timestamp',
        ]);
    }

    /**
     * Test /docs/api route returns OK with HTML (Swagger UI)
     */
    public function test_docs_api_route_exists(): void
    {
        $response = $this->get(route('scramble.docs.ui'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/html; charset=UTF-8');
    }

    /**
     * Test /docs/api.json route returns OK with JSON (OpenAPI spec)
     */
    public function test_docs_api_json_route_exists(): void
    {
        $response = $this->get(route('scramble.docs.document'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/json');
    }

    /**
     * Test /cli returns OK with HTML (Vue.js SPA)
     */
    public function test_cli_spa_returns_html(): void
    {
        $response = $this->get(route('spa'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/html; charset=UTF-8');

        // Check for Vue.js app structure
        $response->assertSee('<div id="app"></div>', false);
        $response->assertViewIs('app');
    }

    /**
     * Test /api.json route returns OK with JSON (API documentation)
     */
    public function test_api_documentation_json_returns_json(): void
    {
        $response = $this->get(route('api.documentation'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/json');
    }
}

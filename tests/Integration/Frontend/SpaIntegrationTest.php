<?php

namespace Tests\Integration\Frontend;

use Tests\TestCase;

/**
 * Integration tests for frontend-dependent routes
 * These tests require frontend build artifacts (Vite manifest, compiled assets)
 */
class SpaIntegrationTest extends TestCase
{
    /**
     * Test /cli returns OK with HTML (Vue.js SPA)
     * This test requires npm run build to have been executed
     */
    public function test_cli_spa_returns_html(): void
    {
        // Skip test if Vite manifest doesn't exist or VITE_ENABLED is false
        $manifestPath = public_path('build/manifest.json');
        $viteEnabled = config('app.vite_enabled', env('VITE_ENABLED', true));

        if (! file_exists($manifestPath) || ! $viteEnabled) {
            $this->markTestSkipped(
                'Frontend build not available. Run "npm run build" and ensure VITE_ENABLED=true before running integration tests.'
            );
        }

        $response = $this->get(route('spa'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/html; charset=UTF-8');

        // Check for Vue.js app structure
        $response->assertSee('<div id="app"></div>', false);
        $response->assertViewIs('app');
    }
}

<?php

namespace Tests\Web\Middleware;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Verify that guest authentication pages are never served from browser cache.
 *
 * Browser caching of pages that embed a CSRF token is the root cause of HTTP 419
 * errors when users log in with saved credentials: a cached page's
 * <meta name="csrf-token"> belongs to a previous or expired session, so every
 * token the JS CSRF-refresh component reads from it will be stale.
 *
 * All Fortify GET routes must carry Cache-Control: no-store so that the browser
 * always fetches a fresh server response with a session-current CSRF token.
 */
class CsrfProtectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Guest-facing GET routes that render a CSRF token and must not be cached.
     *
     * Each entry is [routeName, routeParams].
     */
    public static function guestAuthPageProvider(): array
    {
        return [
            'login page' => ['login', []],
            'forgot password page' => ['password.request', []],
            'password reset page' => ['password.reset', ['token' => 'test-token']],
        ];
    }

    #[DataProvider('guestAuthPageProvider')]
    public function test_auth_page_response_has_no_store_cache_control(string $routeName, array $params): void
    {
        $response = $this->get(route($routeName, $params));

        $this->assertStringContainsString(
            'no-store',
            $response->headers->get('Cache-Control', ''),
            "Route '{$routeName}' must include Cache-Control: no-store to prevent browsers from caching the CSRF token."
        );
    }

    #[DataProvider('guestAuthPageProvider')]
    public function test_auth_page_response_has_pragma_no_cache(string $routeName, array $params): void
    {
        $response = $this->get(route($routeName, $params));

        $this->assertStringContainsString(
            'no-cache',
            $response->headers->get('Pragma', ''),
            "Route '{$routeName}' must include Pragma: no-cache for HTTP/1.0 cache compatibility."
        );
    }

    public function test_registration_page_response_has_no_store_cache_control(): void
    {
        Setting::set('self_registration_enabled', true, 'boolean');

        $response = $this->get(route('register'));

        $response->assertOk();
        $this->assertStringContainsString(
            'no-store',
            $response->headers->get('Cache-Control', ''),
            'The registration page must include Cache-Control: no-store to prevent browsers from caching the CSRF token.'
        );
    }

    public function test_post_request_to_auth_route_does_not_receive_no_store_cache_control(): void
    {
        // POST is not a cacheable method; the middleware must not touch its response headers.
        $response = $this->post(route('login.store'), [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertStringNotContainsString(
            'no-store',
            $response->headers->get('Cache-Control', ''),
            'POST responses must not have Cache-Control: no-store added by the NoCacheHeaders middleware.'
        );
    }
}

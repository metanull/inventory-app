<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NoCacheHeaders
{
    /**
     * Prevent browsers from caching pages that embed a CSRF token.
     *
     * If a guest auth page (login, password reset, etc.) is served from the
     * browser cache, the <meta name="csrf-token"> in the stale HTML belongs to
     * a different or expired session. Any subsequent form submission will be
     * rejected with HTTP 419 even when the JS CSRF-refresh component is in
     * place, because it reads the token from that stale meta tag.
     *
     * Setting Cache-Control: no-store forces a fresh server round-trip on every
     * visit, guaranteeing that the CSRF token in the rendered page always
     * matches the user's current session.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isMethodCacheable()) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
        }

        return $response;
    }
}

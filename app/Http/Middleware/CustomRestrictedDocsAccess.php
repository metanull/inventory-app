<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Gate;

class CustomRestrictedDocsAccess
{
    public function handle($request, \Closure $next)
    {
        // Allow access in local and testing environments
        if (app()->environment(['local', 'testing'])) {
            return $next($request);
        }

        // Check if API documentation is explicitly enabled via environment variable
        // Handle boolean-like values properly (true, "true", "1", 1)
        $apiDocsEnabled = env('API_DOCS_ENABLED', false);
        if (filter_var($apiDocsEnabled, FILTER_VALIDATE_BOOLEAN)) {
            return $next($request);
        }

        if (Gate::allows('viewApiDocs')) {
            return $next($request);
        }

        abort(403);
    }
}

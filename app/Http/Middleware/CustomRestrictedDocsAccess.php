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
        if (env('API_DOCS_ENABLED', false)) {
            return $next($request);
        }

        if (Gate::allows('viewApiDocs')) {
            return $next($request);
        }

        abort(403);
    }
}

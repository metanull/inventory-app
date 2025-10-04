<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequirePermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $user = Auth::user();

        // Check if user has the required permission using Spatie's method
        // Note: Users can have permissions directly or through roles - feature-based authorization
        if (! $user->hasPermissionTo($permission)) {
            return $this->handleUnauthorized($request, "Missing permission: {$permission}");
        }

        return $next($request);
    }

    /**
     * Handle unauthorized access
     */
    private function handleUnauthorized(Request $request, string $reason): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Forbidden.',
                'reason' => $reason,
            ], 403);
        }

        abort(403, $reason);
    }

    /**
     * Redirect to login if not authenticated
     */
    private function redirectToLogin(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        return redirect()->guest(route('login'));
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequireRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! Auth::check()) {
            return $this->redirectToLogin($request);
        }

        $user = Auth::user();

        // Check if user has any roles at all
        if ($user->roles()->count() === 0) {
            return $this->handleUnauthorized($request, 'User has no assigned roles');
        }

        // If roles are specified, check if user has any of them
        if (! empty($roles)) {
            $hasRole = false;
            foreach ($roles as $role) {
                if ($user->hasRole($role)) {
                    $hasRole = true;
                    break;
                }
            }

            if (! $hasRole) {
                return $this->handleUnauthorized($request, 'User does not have required role: '.implode(', ', $roles));
            }
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

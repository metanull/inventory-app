<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSelfRegistrationEnabled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if self-registration is enabled
        $selfRegistrationEnabled = Setting::get('self_registration_enabled', false);

        if (! $selfRegistrationEnabled) {
            // Redirect to login with error message
            return redirect()->route('login')
                ->with('error', 'Self-registration is currently disabled. Please contact an administrator.');
        }

        return $next($request);
    }
}

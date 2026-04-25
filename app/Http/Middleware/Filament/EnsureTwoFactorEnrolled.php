<?php

namespace App\Http\Middleware\Filament;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorEnrolled
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        if (! is_null($user->two_factor_confirmed_at)) {
            return $next($request);
        }

        if (
            $request->routeIs('filament.admin.auth.two-factor-setup') ||
            $request->routeIs('filament.admin.auth.two-factor-challenge') ||
            $request->routeIs('filament.admin.auth.logout')
        ) {
            return $next($request);
        }

        return redirect()->route('filament.admin.auth.two-factor-setup');
    }
}

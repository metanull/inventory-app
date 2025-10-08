<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // Register custom login response to enforce 2FA challenge for email 2FA users
        $this->app->singleton(
            \Laravel\Fortify\Contracts\LoginResponse::class,
            \App\Actions\Fortify\CustomLoginResponse::class
        );

        // Register custom failed two-factor login response to handle email 2FA validation
        $this->app->singleton(
            \Laravel\Fortify\Contracts\FailedTwoFactorLoginResponse::class,
            \App\Http\Responses\CustomFailedTwoFactorLoginResponse::class
        );

        // Register safe two-factor authentication provider to handle invalid base32 secrets
        $this->app->singleton(
            \Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider::class,
            \App\Services\SafeTwoFactorAuthenticationProvider::class
        );

        // Register custom logout response
        $this->app->singleton(
            \Laravel\Fortify\Contracts\LogoutResponse::class,
            \App\Http\Responses\CustomLogoutResponse::class
        );

        // Register custom password reset response
        $this->app->singleton(
            \Laravel\Fortify\Contracts\PasswordResetResponse::class,
            \App\Http\Responses\CustomPasswordResetResponse::class
        );

        // Register custom register response
        $this->app->singleton(
            \Laravel\Fortify\Contracts\RegisterResponse::class,
            \App\Http\Responses\CustomRegisterResponse::class
        );

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            // Allow more attempts in testing environment
            $maxAttempts = app()->environment('testing') ? 50 : 5;

            return Limit::perMinute($maxAttempts)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            // Allow more attempts in testing environment
            $maxAttempts = app()->environment('testing') ? 50 : 5;

            return Limit::perMinute($maxAttempts)->by($request->session()->get('login.id'));
        });

        // Listen for logout events to clear remember tokens and 2FA challenge session
        Event::listen(\Illuminate\Auth\Events\Logout::class, function ($event) {
            if ($event->user) {
                $event->user->setRememberToken(null);
                $event->user->save();
            }

            // Clear 2FA challenge session
            session()->forget('login.id');
        });

        // Configure registration view with self-registration check
        Fortify::registerView(function () {
            // Check if self-registration is enabled
            if (! \App\Models\Setting::get('self_registration_enabled', false)) {
                return redirect()->route('login')
                    ->with('error', 'Self-registration is currently disabled. Please contact an administrator.');
            }

            return view('auth.register');
        });
    }
}

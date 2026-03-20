<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\CustomLoginResponse;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\CustomLogoutResponse;
use App\Http\Responses\CustomPasswordResetResponse;
use App\Http\Responses\CustomRegisterResponse;
use App\Models\Setting;
use App\Services\SafeTwoFactorAuthenticationProvider;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use Laravel\Fortify\Contracts\PasswordResetResponse;
use Laravel\Fortify\Contracts\RegisterResponse;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
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

        // Register custom login response to check email verification
        $this->app->singleton(
            LoginResponse::class,
            CustomLoginResponse::class
        );

        // Register safe two-factor authentication provider to handle invalid base32 secrets
        $this->app->singleton(
            TwoFactorAuthenticationProvider::class,
            SafeTwoFactorAuthenticationProvider::class
        );

        // Register custom logout response
        $this->app->singleton(
            LogoutResponse::class,
            CustomLogoutResponse::class
        );

        // Register custom password reset response
        $this->app->singleton(
            PasswordResetResponse::class,
            CustomPasswordResetResponse::class
        );

        // Register custom register response
        $this->app->singleton(
            RegisterResponse::class,
            CustomRegisterResponse::class
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
        Event::listen(Logout::class, function ($event) {
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
            if (! Setting::get('self_registration_enabled', false)) {
                return redirect()->route('login')
                    ->with('error', 'Self-registration is currently disabled. Please contact an administrator.');
            }

            return view('auth.register');
        });
    }
}

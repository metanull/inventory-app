<?php

namespace App\Providers;

use App\Actions\Jetstream\DeleteUser;
use App\Livewire\Profile\TwoFactorAuthenticationForm;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Laravel\Jetstream\Http\Controllers\Livewire\ApiTokenController;
use Laravel\Jetstream\Http\Controllers\Livewire\UserProfileController;
use Laravel\Jetstream\Jetstream;
use Livewire\Livewire;

class JetstreamServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Disable Jetstream's default route registration
        Jetstream::ignoreRoutes();

        // Register our custom profile route early to take precedence
        $this->registerCustomRoutes();
    }

    /**
     * Register custom routes with /web prefix to match Fortify configuration.
     */
    protected function registerCustomRoutes(): void
    {
        $this->app->booted(function () {
            if (config('jetstream.stack') === 'livewire') {
                Route::middleware(['web', 'auth:sanctum', 'verified'])
                    ->prefix('web')
                    ->group(function () {
                        Route::get('/user/profile', [UserProfileController::class, 'show'])
                            ->name('web.profile.show');

                        // Add API tokens route if API features are enabled
                        if (Jetstream::hasApiFeatures()) {
                            Route::get('/user/api-tokens', [ApiTokenController::class, 'index'])
                                ->name('web.api-tokens.index');
                        }
                    });
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configurePermissions();

        Jetstream::deleteUsersUsing(DeleteUser::class);

        // Override Jetstream's TwoFactorAuthenticationForm with our custom implementation
        Livewire::component('profile.two-factor-authentication-form', TwoFactorAuthenticationForm::class);

        Vite::prefetch(concurrency: 3);
    }

    /**
     * Configure the permissions that are available within the application.
     */
    protected function configurePermissions(): void
    {
        Jetstream::defaultApiTokenPermissions(['read']);

        Jetstream::permissions([
            'create',
            'read',
            'update',
            'delete',
        ]);
    }
}

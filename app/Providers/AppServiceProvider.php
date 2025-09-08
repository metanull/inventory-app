<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
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
        // Define a Gate for API documentation access
        Gate::define('viewApiDocs', function ($user = null) {
            // Allow authenticated users to view API docs
            return $user !== null;
        });

        Scramble::afterOpenApiGenerated(function (OpenApi $openApi) {
            $openApi->secure(
                // SecurityScheme::apiKey('query', 'api_token')
                SecurityScheme::http('bearer')
            );
        });
    }
}

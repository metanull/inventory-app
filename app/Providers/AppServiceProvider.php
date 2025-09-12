<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
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
        // Share version information to all views. This will be resolved from
        // config('app.version') or the VERSION file included by the CI pipeline.
        View::share('app_version_info', function () {
            $info = [
                'version' => config('app.version'),
                'api_client_version' => null,
                'build_timestamp' => null,
                'commit_sha' => null,
            ];

            $versionPath = base_path('VERSION');
            if (file_exists($versionPath)) {
                try {
                    $raw = @file_get_contents($versionPath);
                    $decoded = @json_decode($raw, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        $info['version'] = $decoded['app_version'] ?? $info['version'];
                        $info['api_client_version'] = $decoded['api_client_version'] ?? null;
                        $info['build_timestamp'] = $decoded['build_timestamp'] ?? null;
                        $info['commit_sha'] = $decoded['commit_sha'] ?? null;
                    }
                } catch (\Exception $e) {
                    // swallow errors and continue with whatever info we have
                }
            }

            return $info;
        });

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

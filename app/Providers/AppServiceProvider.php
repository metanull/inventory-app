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
        // Share entity color config helper across views
        View::share('entityColor', function (string $entity): array {
            $map = config('app_entities.colors', []);
            $fragments = config('app_entities.fragments', []);
            $color = $map[$entity] ?? 'gray';
            $fragment = $fragments[$color] ?? [
                'button' => 'bg-gray-600 hover:bg-gray-700 text-white',
                'focus' => 'focus:border-gray-500 focus:ring-gray-500',
                'badge' => 'bg-gray-100 text-gray-700',
                'accentText' => 'text-gray-700',
                'accentLink' => 'text-gray-600 hover:text-gray-800',
                'pill' => 'bg-gray-100 text-gray-600',
            ];

            return array_merge(['name' => $color], $fragment);
        });

        // Share version information to all views. This will be resolved from
        // config('app.version') or the VERSION file included by the CI pipeline.
        View::share('app_version_info', function () {
            // Initialize with null values
            $info = [
                'app_version' => null,
                'api_client_version' => null,
                'build_timestamp' => null,
                'commit_sha' => null,
                'repository' => null,
                'repository_url' => null,
            ];

            // If VERSION file exists, load it
            $versionPath = base_path('VERSION');
            if (file_exists($versionPath)) {
                try {
                    $content = file_get_contents($versionPath);

                    // Remove UTF-8 BOM if present
                    if (substr($content, 0, 3) === "\xEF\xBB\xBF") {
                        $content = substr($content, 3);
                    }

                    $versionData = json_decode($content, true);

                    if (json_last_error() === JSON_ERROR_NONE && is_array($versionData)) {
                        $info = array_merge($info, $versionData);
                    }
                } catch (\Exception $e) {
                    // Continue with null values if file reading fails
                }
            }

            // Fallback to config for app_version if not available from VERSION file
            if (is_null($info['app_version'])) {
                // Try to read version from package.json
                $packagePath = base_path('package.json');
                if (file_exists($packagePath)) {
                    try {
                        $packageContent = file_get_contents($packagePath);
                        $packageData = json_decode($packageContent, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($packageData) && isset($packageData['version'])) {
                            $info['app_version'] = $packageData['version'];
                        }
                    } catch (\Exception $e) {
                        // Continue to next fallback if package.json reading fails
                    }
                }

                // If still null, use config fallback
                if (is_null($info['app_version'])) {
                    $info['app_version'] = config('app.version', 'dev');
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

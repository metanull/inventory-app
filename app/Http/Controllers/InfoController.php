<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * InfoController provides application information and health check endpoints.
 *
 * This controller handles requests for application metadata such as version,
 * health status, and system information that may be needed by monitoring
 * systems or API consumers.
 */
class InfoController extends Controller
{
    /**
     * Get application information including version and health status.
     *
     * Returns basic application information including:
     * - Application name and version
     * - Health check status for key services
     * - Timestamp of the response
     *
     * @return JsonResponse Application information and health status
     */
    public function index(): JsonResponse
    {
        $healthChecks = $this->performHealthChecks();

        $info = [
            'application' => [
                'name' => config('app.name'),
                'version' => $this->getApplicationVersion(),
                'environment' => config('app.env'),
            ],
            'health' => [
                'status' => $healthChecks['overall_status'],
                'checks' => $healthChecks['checks'],
            ],
            'timestamp' => now()->toISOString(),
        ];

        $statusCode = $healthChecks['overall_status'] === 'healthy' ? 200 : 503;

        return response()->json($info, $statusCode);
    }

    /**
     * Get only the health check status.
     *
     * Lightweight endpoint for health monitoring that returns
     * only the essential health status information.
     *
     * @return JsonResponse Health check status
     */
    public function health(): JsonResponse
    {
        $healthChecks = $this->performHealthChecks();

        $health = [
            'status' => $healthChecks['overall_status'],
            'checks' => $healthChecks['checks'],
            'timestamp' => now()->toISOString(),
        ];

        $statusCode = $healthChecks['overall_status'] === 'healthy' ? 200 : 503;

        return response()->json($health, $statusCode);
    }

    /**
     * Get application version information only.
     *
     * Simple endpoint that returns just the version information
     * for deployment tracking and API compatibility checks.
     *
     * @return JsonResponse Version information
     */
    public function version(): JsonResponse
    {
        // If a deployment has produced a VERSION file at repo root, return its content as-is.
        $versionPath = base_path('VERSION');
        if (file_exists($versionPath)) {
            try {
                $raw = @file_get_contents($versionPath);
                $decoded = @json_decode($raw, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    return response()->json($decoded);
                }
            } catch (\Exception $e) {
                // fallthrough to default behaviour below
            }
        }

        // Fallback to existing lightweight version response
        return response()->json([
            'version' => $this->getApplicationVersion(),
            'name' => config('app.name'),
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * Perform health checks on critical application services.
     *
     * Checks the health of key application dependencies including
     * database connectivity and cache functionality.
     *
     * @return array Health check results with overall status
     */
    private function performHealthChecks(): array
    {
        $checks = [];
        $allHealthy = true;

        // Database health check
        try {
            DB::connection()->getPdo();
            DB::select('SELECT 1');
            $checks['database'] = [
                'status' => 'healthy',
                'message' => 'Database connection successful',
            ];
        } catch (\Exception $e) {
            $checks['database'] = [
                'status' => 'unhealthy',
                'message' => 'Database connection failed: '.$e->getMessage(),
            ];
            $allHealthy = false;
        }

        // Cache health check
        try {
            $testKey = 'health_check_'.now()->timestamp;
            $testValue = 'test_value';

            Cache::put($testKey, $testValue, 60);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);

            if ($retrieved === $testValue) {
                $checks['cache'] = [
                    'status' => 'healthy',
                    'message' => 'Cache operations successful',
                ];
            } else {
                $checks['cache'] = [
                    'status' => 'unhealthy',
                    'message' => 'Cache read/write verification failed',
                ];
                $allHealthy = false;
            }
        } catch (\Exception $e) {
            $checks['cache'] = [
                'status' => 'unhealthy',
                'message' => 'Cache operations failed: '.$e->getMessage(),
            ];
            $allHealthy = false;
        }

        return [
            'overall_status' => $allHealthy ? 'healthy' : 'unhealthy',
            'checks' => $checks,
        ];
    }

    /**
     * Get the application version string.
     *
     * Determines the application version from various sources:
     * 1. APP_VERSION environment variable (preferred)
     * 2. Git commit hash if available
     * 3. Default fallback version
     *
     * @return string Application version
     */
    private function getApplicationVersion(): string
    {
        // Check for explicit version in environment
        $envVersion = config('app.version');
        if ($envVersion) {
            return $envVersion;
        }

        // Try to get git commit hash
        try {
            if (file_exists(base_path('.git/HEAD'))) {
                $head = trim(file_get_contents(base_path('.git/HEAD')));

                if (str_starts_with($head, 'ref: ')) {
                    $refPath = base_path('.git/'.substr($head, 5));
                    if (file_exists($refPath)) {
                        $commit = trim(file_get_contents($refPath));

                        return substr($commit, 0, 8); // Short commit hash
                    }
                } else {
                    return substr($head, 0, 8); // Direct commit hash
                }
            }
        } catch (\Exception $e) {
            // Fall through to default
        }

        // Default fallback
        return '1.0.0-dev';
    }
}

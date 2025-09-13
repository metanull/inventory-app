<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\View;

class DebugVersionInfo extends Command
{
    protected $signature = 'debug:version';

    protected $description = 'Debug version information loading';

    public function handle()
    {
        $this->info('=== Laravel Application Version Debug ===');
        $this->newLine();

        // Test the actual View::share callback
        $this->info('1. Testing app_version_info callback from AppServiceProvider:');

        try {
            // Get the shared variable (this will call the callback)
            $versionInfo = View::shared('app_version_info');

            if (is_callable($versionInfo)) {
                $this->info('   ✓ app_version_info is callable');
                $result = $versionInfo();
                $this->info('   ✓ Callback executed successfully');

                // Process the result to include formatted datetime like app-footer.blade.php
                $processedResult = $result;
                if (isset($result['build_timestamp'])) {
                    $buildTimestamp = $result['build_timestamp'];
                    $formattedBuildDate = null;

                    try {
                        // Handle CI/CD format using the "value" field to avoid locale issues
                        if (is_array($buildTimestamp) && isset($buildTimestamp['value'])) {
                            // Parse .NET DateTime format: "/Date(1757794373908)/"
                            $value = $buildTimestamp['value'];
                            if (preg_match('/\/Date\((\d+)\)\//', $value, $matches)) {
                                $timestamp = intval($matches[1]) / 1000; // Convert milliseconds to seconds
                                $date = new \DateTime;
                                $date->setTimestamp($timestamp);
                                $formattedBuildDate = $date->format('d/m/Y H:i');
                            }
                        } elseif (is_string($buildTimestamp)) {
                            // Simple string format fallback
                            $date = new \DateTime($buildTimestamp);
                            $formattedBuildDate = $date->format('d/m/Y H:i');
                        }
                    } catch (\Exception $e) {
                        // If date parsing fails, leave as null
                        $formattedBuildDate = null;
                    }

                    if ($formattedBuildDate) {
                        $processedResult['build_timestamp_formatted'] = $formattedBuildDate;
                    }
                }

                $this->table(['Key', 'Value'], array_map(function ($key, $value) {
                    if (is_array($value)) {
                        $value = json_encode($value);
                    }

                    return [$key, $value ?? 'NULL'];
                }, array_keys($processedResult), array_values($processedResult)));
            } else {
                $this->error('   ✗ app_version_info is not callable');
                $this->line('   Value: '.print_r($versionInfo, true));
            }
        } catch (\Exception $e) {
            $this->error('   ✗ Exception: '.$e->getMessage());
        }

        $this->newLine();
        $this->info('2. Testing file paths:');

        $versionPath = base_path('VERSION');
        $packagePath = base_path('package.json');

        $this->line("   VERSION file path: {$versionPath}");
        $this->line('   VERSION file exists: '.(file_exists($versionPath) ? 'YES' : 'NO'));

        if (file_exists($versionPath)) {
            $this->line('   VERSION file size: '.filesize($versionPath).' bytes');
            $this->line('   VERSION file readable: '.(is_readable($versionPath) ? 'YES' : 'NO'));
        }

        $this->line("   package.json path: {$packagePath}");
        $this->line('   package.json exists: '.(file_exists($packagePath) ? 'YES' : 'NO'));

        $this->newLine();
        $this->info('3. Testing config fallbacks:');
        $this->line("   config('app.version'): ".(config('app.version') ?? 'NULL'));
        $this->line("   env('APP_VERSION'): ".(env('APP_VERSION') ?? 'NULL'));

        $this->newLine();
        $this->info('=== End Debug ===');

        return 0;
    }
}

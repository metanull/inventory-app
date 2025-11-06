<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\DownCommand;

/**
 * Extended down command that creates a public down.lock file
 * for SPA frontend detection of maintenance mode
 */
class CustomDownCommand extends DownCommand
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Call parent implementation to execute standard Laravel maintenance mode
        $result = parent::handle();

        // Create down.lock file in public directory for SPA access
        try {
            $lockFilePath = public_path('down.lock');
            file_put_contents($lockFilePath, json_encode([
                'timestamp' => now()->toIso8601String(),
                'message' => 'Application is currently under maintenance',
            ]));

            $this->components->info('Created public/down.lock for SPA detection');
        } catch (\Exception $e) {
            $this->components->warn('Failed to create public/down.lock: '.$e->getMessage());
        }

        return $result;
    }
}

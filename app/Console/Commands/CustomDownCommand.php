<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\DownCommand;
use Illuminate\Support\Facades\Storage;

/**
 * Extended down command that creates a public down.lock file
 * for SPA frontend detection of maintenance mode
 */
class CustomDownCommand extends DownCommand
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // Call parent implementation to execute standard Laravel maintenance mode
        parent::handle();

        // Create lock file in public directory for SPA access
        try {
            $disk = Storage::disk(config('maintenance.public_lock_disk'));
            $filename = config('maintenance.public_lock_file');

            $content = json_encode([
                'timestamp' => now()->toIso8601String(),
                'message' => 'Application is currently under maintenance',
            ]);

            $disk->put($filename, $content);

            $this->components->info("Created public/{$filename} for SPA detection");
        } catch (\Exception $e) {
            $this->components->warn('Failed to create public lock file: '.$e->getMessage());
        }
    }
}

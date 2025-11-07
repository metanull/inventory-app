<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\UpCommand;
use Illuminate\Support\Facades\Storage;

/**
 * Extended up command that removes the public down.lock file
 * for SPA frontend detection of maintenance mode recovery
 */
class CustomUpCommand extends UpCommand
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // Call parent implementation to bring application out of maintenance mode
        parent::handle();

        // Remove down.lock file from public directory
        try {
            $disk = Storage::build([
                'driver' => 'local',
                'root' => base_path('public'),
            ]);

            if ($disk->exists('down.lock')) {
                $disk->delete('down.lock');
                $this->components->info('Removed public/down.lock');
            }
        } catch (\Exception $e) {
            $this->components->warn('Failed to remove public/down.lock: '.$e->getMessage());
        }
    }
}

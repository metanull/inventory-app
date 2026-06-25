<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\UpCommand;
use Illuminate\Support\Facades\Config;
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
    public function handle(): int
    {
        // Call parent implementation to bring application out of maintenance mode
        $result = parent::handle();

        // Remove lock file from public directory
        try {
            $disk = Storage::disk(Config::string('maintenance.public_lock_disk'));
            $filename = Config::string('maintenance.public_lock_file');

            if ($disk->exists($filename)) {
                $disk->delete($filename);
                $this->components->info("Removed public/{$filename}");
            }
        } catch (\Exception $e) {
            $this->components->warn('Failed to remove public lock file: '.$e->getMessage());
        }

        return $result;
    }
}

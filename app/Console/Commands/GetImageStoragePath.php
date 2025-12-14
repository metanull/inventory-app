<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GetImageStoragePath extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:image-path 
                            {type=pictures : The image storage type (uploads, available, or pictures)}
                            {--json : Output as JSON}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get the absolute path to an image storage directory';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $type = $this->argument('type');

        // Validate type
        if (! in_array($type, ['uploads', 'available', 'pictures'])) {
            $this->error("Invalid storage type '{$type}'. Valid types are: uploads, available, pictures");

            return 1;
        }

        // Get config based on type
        [$disk, $directory] = $this->getStorageConfig($type);

        // Get the absolute path to the disk root
        $diskPath = Storage::disk($disk)->path('');

        // Combine with the directory (trim slashes to avoid double slashes)
        $fullPath = rtrim($diskPath, '/\\');
        if ($directory) {
            $fullPath .= DIRECTORY_SEPARATOR.trim($directory, '/\\');
        }

        // Normalize path separators for the current OS
        $fullPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $fullPath);

        if ($this->option('json')) {
            $this->line(json_encode([
                'type' => $type,
                'disk' => $disk,
                'directory' => $directory,
                'absolute_path' => $fullPath,
            ], JSON_PRETTY_PRINT));
        } else {
            // Use line() instead of info() to avoid adding </info> tag
            $this->line($fullPath);
        }

        return 0;
    }

    /**
     * Get storage configuration for the specified type.
     *
     * @return array{0: string|null, 1: string|null}
     */
    private function getStorageConfig(string $type): array
    {
        return match ($type) {
            'uploads' => [
                config('localstorage.uploads.images.disk'),
                config('localstorage.uploads.images.directory'),
            ],
            'available' => [
                config('localstorage.available.images.disk'),
                config('localstorage.available.images.directory'),
            ],
            'pictures' => [
                config('localstorage.pictures.disk'),
                config('localstorage.pictures.directory'),
            ],
        };
    }
}

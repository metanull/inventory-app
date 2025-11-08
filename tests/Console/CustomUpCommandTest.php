<?php

namespace Tests\Console;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Unit tests for CustomUpCommand.
 *
 * These tests focus on our custom business logic: removing the public lock
 * file via Storage. We use Storage::fake() for complete isolation and don't test
 * Laravel's maintenance mode functionality (framework responsibility).
 */
class CustomUpCommandTest extends TestCase
{
    private string $disk;

    private string $filename;

    protected function setUp(): void
    {
        parent::setUp();

        $this->disk = config('maintenance.public_lock_disk');
        $this->filename = config('maintenance.public_lock_file');
    }

    public function test_command_removes_existing_lock_file(): void
    {
        Storage::fake($this->disk);

        // Create a lock file first
        Storage::disk($this->disk)->put($this->filename, json_encode([
            'timestamp' => now()->toIso8601String(),
            'message' => 'Application is currently under maintenance',
        ]));

        $this->assertTrue(Storage::disk($this->disk)->exists($this->filename));

        // Run up command
        $this->artisan('up');

        // Verify file was deleted
        $this->assertFalse(Storage::disk($this->disk)->exists($this->filename));
    }

    public function test_command_succeeds_when_lock_file_does_not_exist(): void
    {
        Storage::fake($this->disk);

        // Ensure file doesn't exist
        $this->assertFalse(Storage::disk($this->disk)->exists($this->filename));

        // Command should succeed without errors
        $this->artisan('up')->assertSuccessful();

        // File should still not exist
        $this->assertFalse(Storage::disk($this->disk)->exists($this->filename));
    }

    public function test_command_removes_orphaned_lock_file(): void
    {
        Storage::fake($this->disk);

        // Create an orphaned lock file (simulating leftover from crashed maintenance)
        Storage::disk($this->disk)->put($this->filename, json_encode([
            'timestamp' => now()->subHours(2)->toIso8601String(),
            'message' => 'Old maintenance message',
        ]));

        $this->assertTrue(Storage::disk($this->disk)->exists($this->filename));

        // Run up command to clean up
        $this->artisan('up');

        // Verify orphaned file was removed
        $this->assertFalse(Storage::disk($this->disk)->exists($this->filename));
    }
}

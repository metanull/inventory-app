<?php

namespace Tests\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomUpCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure app is up before each test
        if (app()->isDownForMaintenance()) {
            $this->artisan('up');
        }
    }

    protected function tearDown(): void
    {
        // Clean up down.lock file after each test
        $lockFilePath = public_path('down.lock');
        if (file_exists($lockFilePath)) {
            unlink($lockFilePath);
        }

        // Ensure app is up
        if (app()->isDownForMaintenance()) {
            $this->artisan('up');
        }

        parent::tearDown();
    }

    public function test_command_removes_down_lock_file(): void
    {
        $lockFilePath = public_path('down.lock');

        // Put app in maintenance mode (creates down.lock)
        $this->artisan('down');
        $this->assertFileExists($lockFilePath);

        // Bring app back up
        $this->artisan('up');

        // Verify down.lock was removed
        $this->assertFileDoesNotExist($lockFilePath);
    }

    public function test_command_brings_application_out_of_maintenance_mode(): void
    {
        // Put app in maintenance mode
        $this->artisan('down');
        $this->assertTrue(app()->isDownForMaintenance());

        // Bring app back up
        $this->artisan('up');

        $this->assertFalse(app()->isDownForMaintenance());
    }

    public function test_command_succeeds_when_down_lock_does_not_exist(): void
    {
        $lockFilePath = public_path('down.lock');

        // Ensure down.lock doesn't exist
        if (file_exists($lockFilePath)) {
            unlink($lockFilePath);
        }

        // Command should still succeed
        $this->artisan('up');

        $this->assertFileDoesNotExist($lockFilePath);
    }

    public function test_command_succeeds_when_app_not_in_maintenance_mode(): void
    {
        $lockFilePath = public_path('down.lock');

        // Create down.lock file manually (simulating orphaned lock file)
        file_put_contents($lockFilePath, json_encode([
            'timestamp' => now()->toIso8601String(),
            'message' => 'Application is currently under maintenance',
        ]));

        $this->assertFileExists($lockFilePath);
        $this->assertFalse(app()->isDownForMaintenance());

        // Command should clean up the orphaned lock file
        $this->artisan('up');

        $this->assertFileDoesNotExist($lockFilePath);
    }

    public function test_down_and_up_cycle_works_correctly(): void
    {
        $lockFilePath = public_path('down.lock');

        // Initial state
        $this->assertFalse(app()->isDownForMaintenance());
        $this->assertFileDoesNotExist($lockFilePath);

        // Put down
        $this->artisan('down');
        $this->assertTrue(app()->isDownForMaintenance());
        $this->assertFileExists($lockFilePath);

        // Bring back up
        $this->artisan('up');
        $this->assertFalse(app()->isDownForMaintenance());
        $this->assertFileDoesNotExist($lockFilePath);
    }
}

<?php

namespace Tests\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomDownCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        // Clean up down.lock file after each test
        $lockFilePath = public_path('down.lock');
        if (file_exists($lockFilePath)) {
            unlink($lockFilePath);
        }

        // Bring app back up if it was put down
        if (app()->isDownForMaintenance()) {
            $this->artisan('up');
        }

        parent::tearDown();
    }

    public function test_command_creates_down_lock_file(): void
    {
        $lockFilePath = public_path('down.lock');

        // Ensure file doesn't exist before test
        if (file_exists($lockFilePath)) {
            unlink($lockFilePath);
        }

        $this->artisan('down');

        // Verify down.lock file was created
        $this->assertFileExists($lockFilePath);
    }

    public function test_down_lock_file_contains_valid_json(): void
    {
        $lockFilePath = public_path('down.lock');

        $this->artisan('down');

        $this->assertFileExists($lockFilePath);

        // Verify file contains valid JSON
        $content = file_get_contents($lockFilePath);
        $data = json_decode($content, true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertArrayHasKey('message', $data);
    }

    public function test_down_lock_file_contains_timestamp(): void
    {
        $lockFilePath = public_path('down.lock');

        $this->artisan('down');

        $content = file_get_contents($lockFilePath);
        $data = json_decode($content, true);

        $this->assertNotEmpty($data['timestamp']);

        // Verify timestamp is in ISO8601 format
        $timestamp = \Carbon\Carbon::parse($data['timestamp']);
        $this->assertInstanceOf(\Carbon\Carbon::class, $timestamp);
    }

    public function test_down_lock_file_contains_message(): void
    {
        $lockFilePath = public_path('down.lock');

        $this->artisan('down');

        $content = file_get_contents($lockFilePath);
        $data = json_decode($content, true);

        $this->assertEquals('Application is currently under maintenance', $data['message']);
    }

    public function test_command_puts_application_in_maintenance_mode(): void
    {
        $this->assertFalse(app()->isDownForMaintenance());

        $this->artisan('down');

        $this->assertTrue(app()->isDownForMaintenance());
    }

    public function test_command_works_with_retry_option(): void
    {
        $lockFilePath = public_path('down.lock');

        $this->artisan('down', ['--retry' => 60]);

        $this->assertFileExists($lockFilePath);
        $this->assertTrue(app()->isDownForMaintenance());
    }

    public function test_command_works_with_secret_option(): void
    {
        $lockFilePath = public_path('down.lock');

        $this->artisan('down', ['--secret' => 'test-secret']);

        $this->assertFileExists($lockFilePath);
        $this->assertTrue(app()->isDownForMaintenance());
    }
}

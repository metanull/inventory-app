<?php

namespace Tests\Console;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Tests\Traits\FakesMaintenanceMode;

/**
 * Unit tests for CustomDownCommand.
 *
 * These tests focus on our custom business logic: creating the public lock
 * file via Storage. We use Storage::fake() for complete isolation and don't test
 * Laravel's maintenance mode functionality (framework responsibility).
 *
 * Storage::fake() only isolates the public lock disk, so the maintenance driver
 * is faked too — otherwise `down` writes the shared storage/framework/down file
 * and every parallel worker's HTTP tests start returning 503. See
 * FakesMaintenanceMode.
 */
class CustomDownCommandTest extends TestCase
{
    use FakesMaintenanceMode;

    private string $disk;

    private string $filename;

    private bool $maintenanceStubPreexisted = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fakeMaintenanceMode();

        $this->disk = config('maintenance.public_lock_disk');
        $this->filename = config('maintenance.public_lock_file');

        // The parent DownCommand writes this stub directly, outside the driver.
        // It is inert without storage/framework/down, but it is ours to clean up.
        $this->maintenanceStubPreexisted = is_file(storage_path('framework/maintenance.php'));
    }

    protected function tearDown(): void
    {
        if (! $this->maintenanceStubPreexisted && is_file($stub = storage_path('framework/maintenance.php'))) {
            @unlink($stub);
        }

        parent::tearDown();
    }

    public function test_command_creates_lock_file(): void
    {
        Storage::fake($this->disk);

        $this->artisan('down');

        $this->assertTrue(Storage::disk($this->disk)->exists($this->filename));
    }

    public function test_lock_file_contains_valid_json(): void
    {
        Storage::fake($this->disk);

        $this->artisan('down');

        $content = Storage::disk($this->disk)->get($this->filename);
        $data = json_decode($content, true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('timestamp', $data);
        $this->assertArrayHasKey('message', $data);
    }

    public function test_lock_file_contains_valid_timestamp(): void
    {
        Storage::fake($this->disk);

        $this->artisan('down');

        $content = Storage::disk($this->disk)->get($this->filename);
        $data = json_decode($content, true);

        $this->assertNotEmpty($data['timestamp']);

        // Verify timestamp is in ISO8601 format
        $timestamp = Carbon::parse($data['timestamp']);
        $this->assertInstanceOf(Carbon::class, $timestamp);
    }

    public function test_lock_file_contains_correct_message(): void
    {
        Storage::fake($this->disk);

        $this->artisan('down');

        $content = Storage::disk($this->disk)->get($this->filename);
        $data = json_decode($content, true);

        $this->assertEquals('Application is currently under maintenance', $data['message']);
    }
}

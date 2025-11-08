<?php

namespace Tests\Console;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Unit tests for CustomDownCommand.
 *
 * These tests focus on our custom business logic: creating the public lock
 * file via Storage. We use Storage::fake() for complete isolation and don't test
 * Laravel's maintenance mode functionality (framework responsibility).
 */
class CustomDownCommandTest extends TestCase
{
    private string $disk;

    private string $filename;

    protected function setUp(): void
    {
        parent::setUp();

        $this->disk = config('maintenance.public_lock_disk');
        $this->filename = config('maintenance.public_lock_file');
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
        $timestamp = \Carbon\Carbon::parse($data['timestamp']);
        $this->assertInstanceOf(\Carbon\Carbon::class, $timestamp);
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

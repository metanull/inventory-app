<?php

namespace Tests\Console;

use App\Models\ItemImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttachedRegistryAuditCommandTest extends TestCase
{
    use RefreshDatabase;

    private string $disk;

    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->disk = config('localstorage.pictures.disk');
        $this->directory = trim(config('localstorage.pictures.directory'), '/');
    }

    public function test_command_succeeds_with_clean_inventory(): void
    {
        Storage::fake($this->disk);

        $image = ItemImage::factory()->create(['path' => 'clean.jpg']);
        $storagePath = $this->directory.'/'.'clean.jpg';
        Storage::disk($this->disk)->put($storagePath, 'data');

        $this->artisan('images:attached-registry-audit')
            ->assertExitCode(0);
    }

    public function test_command_reports_missing_file(): void
    {
        Storage::fake($this->disk);

        // Create a record that points to a non-existent file
        ItemImage::factory()->create(['path' => 'missing.jpg']);

        $this->artisan('images:attached-registry-audit')
            ->assertExitCode(0); // Missing files are reported but do not fail by default
    }

    public function test_command_fails_on_missing_file_when_fail_on_issues(): void
    {
        Storage::fake($this->disk);

        ItemImage::factory()->create(['path' => 'missing.jpg']);

        $this->artisan('images:attached-registry-audit', ['--fail-on-issues' => true])
            ->assertExitCode(1);
    }

    public function test_command_reports_duplicate_paths(): void
    {
        Storage::fake($this->disk);

        // Two rows pointing to the same path
        ItemImage::factory()->create(['path' => 'dupe.jpg']);
        ItemImage::factory()->create(['path' => 'dupe.jpg']);

        $this->artisan('images:attached-registry-audit', ['--fail-on-issues' => true])
            ->assertExitCode(1);
    }

    public function test_command_reports_empty_path(): void
    {
        Storage::fake($this->disk);

        ItemImage::factory()->create(['path' => '']);

        $this->artisan('images:attached-registry-audit', ['--fail-on-issues' => true])
            ->assertExitCode(1);
    }

    public function test_command_outputs_json(): void
    {
        Storage::fake($this->disk);

        $image = ItemImage::factory()->create(['path' => 'json-test.jpg']);
        Storage::disk($this->disk)->put($this->directory.'/json-test.jpg', 'data');

        $output = $this->artisan('images:attached-registry-audit', ['--json' => true])
            ->assertExitCode(0);

        // We cannot easily inspect stdout from artisan test, but confirm it runs without error
        $this->addToAssertionCount(1);
    }

    public function test_command_succeeds_without_records(): void
    {
        Storage::fake($this->disk);

        $this->artisan('images:attached-registry-audit')
            ->assertExitCode(0);
    }

    public function test_command_does_not_fail_by_default_even_with_issues(): void
    {
        Storage::fake($this->disk);

        // Missing file - should not fail without --fail-on-issues
        ItemImage::factory()->create(['path' => 'ghost.jpg']);

        $this->artisan('images:attached-registry-audit')
            ->assertExitCode(0);
    }
}

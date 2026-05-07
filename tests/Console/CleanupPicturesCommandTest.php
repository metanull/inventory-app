<?php

namespace Tests\Console;

use App\Models\ItemImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CleanupPicturesCommandTest extends TestCase
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

    public function test_dry_run_does_not_delete_orphaned_files(): void
    {
        Storage::fake($this->disk);

        $orphanPath = $this->directory.'/orphan.jpg';
        Storage::disk($this->disk)->put($orphanPath, 'orphan-data');

        $this->artisan('images:cleanup-pictures')
            ->assertExitCode(0);

        $this->assertTrue(Storage::disk($this->disk)->exists($orphanPath));
    }

    public function test_dry_run_reports_orphan_candidates(): void
    {
        Storage::fake($this->disk);

        Storage::disk($this->disk)->put($this->directory.'/orphan1.jpg', 'data');
        Storage::disk($this->disk)->put($this->directory.'/orphan2.jpg', 'data');

        $this->artisan('images:cleanup-pictures')
            ->expectsOutputToContain('orphan')
            ->assertExitCode(0);
    }

    public function test_delete_option_removes_orphaned_files(): void
    {
        Storage::fake($this->disk);

        $orphanPath = $this->directory.'/orphan.jpg';
        Storage::disk($this->disk)->put($orphanPath, 'orphan-data');

        $this->artisan('images:cleanup-pictures', [
            '--delete' => true,
            '--force' => true,
        ])->assertExitCode(0);

        $this->assertFalse(Storage::disk($this->disk)->exists($orphanPath));
    }

    public function test_referenced_files_are_not_deleted(): void
    {
        Storage::fake($this->disk);

        $image = ItemImage::factory()->create(['path' => 'referenced.jpg']);
        $referencedPath = $this->directory.'/referenced.jpg';
        Storage::disk($this->disk)->put($referencedPath, 'image-data');

        $orphanPath = $this->directory.'/orphan.jpg';
        Storage::disk($this->disk)->put($orphanPath, 'orphan-data');

        $this->artisan('images:cleanup-pictures', [
            '--delete' => true,
            '--force' => true,
        ])->assertExitCode(0);

        $this->assertTrue(Storage::disk($this->disk)->exists($referencedPath));
        $this->assertFalse(Storage::disk($this->disk)->exists($orphanPath));
    }

    public function test_confirmation_is_required_without_force(): void
    {
        Storage::fake($this->disk);

        Storage::disk($this->disk)->put($this->directory.'/orphan.jpg', 'data');

        $this->artisan('images:cleanup-pictures', ['--delete' => true])
            ->expectsConfirmation(
                "Delete 1 orphaned file(s) from disk '{$this->disk}'?",
                'no'
            )
            ->expectsOutput('Aborted. No files were deleted.')
            ->assertExitCode(0);
    }

    public function test_limit_restricts_number_of_deletions(): void
    {
        Storage::fake($this->disk);

        Storage::disk($this->disk)->put($this->directory.'/a.jpg', 'data1');
        Storage::disk($this->disk)->put($this->directory.'/b.jpg', 'data2');
        Storage::disk($this->disk)->put($this->directory.'/c.jpg', 'data3');

        $this->artisan('images:cleanup-pictures', [
            '--delete' => true,
            '--force' => true,
            '--limit' => '1',
        ])->assertExitCode(0);

        $existing = array_filter([
            Storage::disk($this->disk)->exists($this->directory.'/a.jpg'),
            Storage::disk($this->disk)->exists($this->directory.'/b.jpg'),
            Storage::disk($this->disk)->exists($this->directory.'/c.jpg'),
        ]);

        // Exactly 2 files should still exist (only 1 was deleted)
        $this->assertCount(2, $existing);
    }

    public function test_older_than_filter_skips_recent_files(): void
    {
        Storage::fake($this->disk);

        $orphanPath = $this->directory.'/recent.jpg';
        Storage::disk($this->disk)->put($orphanPath, 'recent-data');

        // --older-than=1h means files must be older than 1 hour; a freshly-created file is skipped
        $this->artisan('images:cleanup-pictures', [
            '--delete' => true,
            '--force' => true,
            '--older-than' => '1h',
        ])->assertExitCode(0);

        // The recent file should still exist because it was created "now"
        $this->assertTrue(Storage::disk($this->disk)->exists($orphanPath));
    }

    public function test_json_output_succeeds(): void
    {
        Storage::fake($this->disk);

        Storage::disk($this->disk)->put($this->directory.'/orphan.jpg', 'data');

        $this->artisan('images:cleanup-pictures', ['--json' => true])
            ->assertExitCode(0);
    }

    public function test_registry_is_used_as_source_of_truth(): void
    {
        Storage::fake($this->disk);

        // Create a registered model with a referenced file
        ItemImage::factory()->create(['path' => 'registered.jpg']);
        Storage::disk($this->disk)->put($this->directory.'/registered.jpg', 'image-data');

        // Orphan file
        Storage::disk($this->disk)->put($this->directory.'/orphan.jpg', 'orphan-data');

        $this->artisan('images:cleanup-pictures', [
            '--delete' => true,
            '--force' => true,
        ])->assertExitCode(0);

        $this->assertTrue(Storage::disk($this->disk)->exists($this->directory.'/registered.jpg'));
        $this->assertFalse(Storage::disk($this->disk)->exists($this->directory.'/orphan.jpg'));
    }

    public function test_invalid_limit_returns_failure(): void
    {
        Storage::fake($this->disk);

        $this->artisan('images:cleanup-pictures', ['--limit' => 'abc'])
            ->assertExitCode(1);
    }

    public function test_invalid_older_than_returns_failure(): void
    {
        Storage::fake($this->disk);

        $this->artisan('images:cleanup-pictures', ['--older-than' => 'invalid'])
            ->assertExitCode(1);
    }

    public function test_no_orphans_dry_run_succeeds(): void
    {
        Storage::fake($this->disk);

        $image = ItemImage::factory()->create(['path' => 'clean.jpg']);
        Storage::disk($this->disk)->put($this->directory.'/clean.jpg', 'image-data');

        $this->artisan('images:cleanup-pictures')
            ->assertExitCode(0);
    }
}

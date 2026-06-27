<?php

namespace App\Console\Commands;

use App\Support\Images\AttachedImageRegistry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;

class AttachedRegistryAudit extends Command
{
    protected $signature = 'images:attached-registry-audit
                            {--json : Output results as JSON}
                            {--fail-on-issues : Return a non-zero exit code when any issue is detected}';

    protected $description = 'Audit the attached-image registry: report row counts, missing files, duplicate paths, and empty paths per registered model';

    public function handle(): int
    {
        try {
            AttachedImageRegistry::validate();
        } catch (\RuntimeException $e) {
            $this->error('Registry validation failed: '.$e->getMessage());

            return Command::FAILURE;
        }

        $disk = Config::string('localstorage.pictures.disk');
        $directory = trim(Config::string('localstorage.pictures.directory'), '/');

        $results = [];
        $hasIssues = false;

        foreach (AttachedImageRegistry::modelClasses() as $class) {
            $instance = new $class;
            $table = $instance->getTable();
            $rowCount = 0;
            $paths = [];
            $missingFiles = [];
            $emptyPaths = [];

            $class::query()->chunkById(500, function ($records) use (
                $disk,
                $directory,
                &$rowCount,
                &$paths,
                &$missingFiles,
                &$emptyPaths,
            ) {
                foreach ($records as $record) {
                    $rowCount++;
                    $path = $record->getAttribute('path');

                    if (! is_string($path) || $path === '') {
                        $keyRaw = $record->getKey();
                        $emptyPaths[] = is_scalar($keyRaw) ? (string) $keyRaw : '';

                        continue;
                    }

                    $storagePath = trim($directory, '/').'/'.$path;
                    $paths[] = $storagePath;

                    if (! Storage::disk($disk)->exists($storagePath)) {
                        $missingFiles[] = $storagePath;
                    }
                }
            });

            $allPaths = array_count_values($paths);
            $duplicatePaths = array_keys(array_filter($allPaths, fn ($count) => $count > 1));
            $distinctPaths = array_keys($allPaths);

            if ($missingFiles || $duplicatePaths || $emptyPaths) {
                $hasIssues = true;
            }

            $results[] = [
                'class' => $class,
                'table' => $table,
                'row_count' => $rowCount,
                'distinct_paths' => count($distinctPaths),
                'missing_files' => $missingFiles,
                'duplicate_paths' => $duplicatePaths,
                'empty_path_ids' => $emptyPaths,
            ];
        }

        if ($this->option('json')) {
            $this->line((string) json_encode([
                'registry_valid' => true,
                'has_issues' => $hasIssues,
                'models' => $results,
            ], JSON_PRETTY_PRINT));
        } else {
            $this->outputTable($results, $hasIssues);
        }

        if ($this->option('fail-on-issues') && $hasIssues) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * @param  list<array{class: class-string, table: string, row_count: int, distinct_paths: int, missing_files: list<string>, duplicate_paths: list<string>, empty_path_ids: list<string>}>  $results
     */
    private function outputTable(array $results, bool $hasIssues): void
    {
        $this->info('Attached Image Registry Audit');
        $this->info(str_repeat('=', 60));

        foreach ($results as $result) {
            $this->newLine();
            $this->line("<fg=cyan>{$result['class']}</>");
            $this->line("  Table:           {$result['table']}");
            $this->line("  Rows:            {$result['row_count']}");
            $this->line("  Distinct paths:  {$result['distinct_paths']}");

            if (! empty($result['missing_files'])) {
                $this->warn('  Missing files ('.count($result['missing_files']).')');
                foreach ($result['missing_files'] as $path) {
                    $this->line("    - {$path}");
                }
            }

            if (! empty($result['duplicate_paths'])) {
                $this->warn('  Duplicate paths ('.count($result['duplicate_paths']).')');
                foreach ($result['duplicate_paths'] as $path) {
                    $this->line("    - {$path}");
                }
            }

            if (! empty($result['empty_path_ids'])) {
                $this->warn('  Empty path rows ('.count($result['empty_path_ids']).')');
                foreach ($result['empty_path_ids'] as $id) {
                    $this->line("    - ID: {$id}");
                }
            }

            if (empty($result['missing_files']) && empty($result['duplicate_paths']) && empty($result['empty_path_ids'])) {
                $this->line('  <fg=green>No issues found.</>');
            }
        }

        $this->newLine();
        $this->info(str_repeat('=', 60));

        if ($hasIssues) {
            $this->warn('Audit complete: issues detected (see above).');
        } else {
            $this->info('Audit complete: no issues found.');
        }
    }
}

<?php

namespace App\Console\Commands;

use App\Models\AvailableImage;
use App\Models\ImageUpload;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class QueueStatusCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:status {--detailed : Show detailed job information}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display queue status and image processing information';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== QUEUE STATUS CHECK ===');

        // Check jobs table
        $pendingJobs = DB::table('jobs')->count();
        $this->line("Pending jobs in queue: <fg=yellow>$pendingJobs</>");

        $failedJobs = DB::table('failed_jobs')->count();
        if ($failedJobs > 0) {
            $this->line("Failed jobs: <fg=red>$failedJobs</>");
        } else {
            $this->line("Failed jobs: <fg=green>$failedJobs</>");
        }

        // Check ImageUpload records
        $imageUploads = ImageUpload::count();
        $this->line("Total ImageUploads: <fg=cyan>$imageUploads</>");

        // Check AvailableImage records
        $availableImages = AvailableImage::count();
        $this->line("Total AvailableImages: <fg=cyan>$availableImages</>");

        $this->newLine();
        $this->info('=== RECENT IMAGE UPLOADS ===');

        $recentUploads = ImageUpload::latest()->take(5)->get();
        if ($recentUploads->isEmpty()) {
            $this->line('<fg=gray>No image uploads found</>');
        } else {
            foreach ($recentUploads as $upload) {
                $this->line("- ID: <fg=yellow>{$upload->id}</>");
                $this->line("  Path: {$upload->path}");
                $this->line("  Name: {$upload->name}");
                $this->line('  Size: '.number_format($upload->size).' bytes');
                $this->line("  Created: {$upload->created_at}");
                $this->line("  Updated: {$upload->updated_at}");
                $this->newLine();
            }
        }

        $this->info('=== RECENT AVAILABLE IMAGES ===');
        $recentImages = AvailableImage::latest()->take(5)->get();
        if ($recentImages->isEmpty()) {
            $this->line('<fg=gray>No available images found</>');
        } else {
            foreach ($recentImages as $image) {
                $this->line("- ID: <fg=yellow>{$image->id}</>");
                $this->line("  Path: {$image->path}");
                $this->line('  Comment: '.($image->comment ?: '<fg=gray>No comment</>'));
                $this->line("  Created: {$image->created_at}");
                $this->line("  Updated: {$image->updated_at}");
                $this->newLine();
            }
        }

        // Check if there are any specific job payloads we can inspect
        if ($pendingJobs > 0) {
            $this->info('=== PENDING JOBS DETAILS ===');
            $jobs = DB::table('jobs')->orderBy('created_at', 'desc')->take(5)->get();
            foreach ($jobs as $job) {
                $this->line("- Job ID: <fg=yellow>{$job->id}</>");
                $this->line("  Queue: {$job->queue}");
                $this->line("  Attempts: {$job->attempts}");
                $this->line('  Available at: '.date('Y-m-d H:i:s', $job->available_at));
                $this->line('  Created at: '.date('Y-m-d H:i:s', $job->created_at));

                if ($this->option('detailed')) {
                    $payload = json_decode($job->payload, true);
                    if ($payload && isset($payload['displayName'])) {
                        $this->line("  Job Type: {$payload['displayName']}");
                    }
                }
                $this->newLine();
            }
        }

        $this->info('=== QUEUE WORKER STATUS ===');
        if ($pendingJobs > 0) {
            $this->line('<fg=red>There are pending jobs. Start the queue worker with:</>');
            $this->line('<fg=yellow>php artisan queue:work</>');
        } else {
            $this->line('<fg=green>No pending jobs in the queue.</>');
        }

        return 0;
    }
}

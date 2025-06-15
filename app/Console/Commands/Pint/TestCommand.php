<?php

namespace App\Console\Commands\Pint;

use Illuminate\Console\Command;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pint:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the Pint linter in test mode (reports all errors)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $process = new Process([base_path('vendor\\bin\\pint'), '--test', '--no-interaction', '--ansi']);
        $process->run();
        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        // Output the result of the Pint command
        $this->info($process->getOutput());
    }
}

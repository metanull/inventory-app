<?php

namespace App\Console\Commands\Pint;

use Illuminate\Console\Command;

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
        $output = shell_exec(base_path('vendor/bin/pint').' --test --no-interaction --ansi');
        $this->info($output);
    }
}

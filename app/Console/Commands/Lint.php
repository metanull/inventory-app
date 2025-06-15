<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Lint extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lint';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the Pint linter';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->call('pint:repair');
    }
}

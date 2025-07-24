<?php

namespace App\Console\Commands;

use Database\Seeders\ProductionDataSeeder;
use Illuminate\Console\Command;

class ImportProductionData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:import-production 
                            {--force : Force import even in non-production environments}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import production country and language data from JSON files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (! app()->environment('production') && ! $this->option('force')) {
            $this->error('This command should only be run in production environment.');
            $this->info('Use --force to override this check.');

            return Command::FAILURE;
        }

        $this->info('Starting production data import...');

        try {
            $seeder = new class extends ProductionDataSeeder
            {
                public function run(): void
                {
                    // Force import regardless of environment
                    $this->importCountries();
                    $this->importLanguages();
                }
            };

            $seeder->setCommand($this);
            $seeder->run();

            $this->info('Production data (Languages, Countries) import completed successfully.');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error importing production data: '.$e->getMessage());

            return Command::FAILURE;
        }
    }
}

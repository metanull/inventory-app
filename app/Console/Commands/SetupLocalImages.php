<?php

namespace App\Console\Commands;

use App\Faker\LocalImageProvider;
use Illuminate\Console\Command;

class SetupLocalImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:setup-local-images 
                            {--check : Only check if local images are available}
                            {--download : Download missing images from picsum.photos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup and validate local images for database seeding';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $provider = new LocalImageProvider(fake());

        if ($this->option('check')) {
            return $this->checkLocalImages($provider);
        }

        if ($this->option('download')) {
            return $this->downloadMissingImages($provider);
        }

        // Default: check and setup if needed
        if ($provider->validateSeedImages()) {
            $this->info('✅ All local seed images are available.');
            $this->info('💡 Run "php artisan db:seed --class=FastDatabaseSeeder" for fast seeding.');

            return 0;
        }

        $this->warn('⚠️  Some local seed images are missing.');
        $this->info('💡 Run the download script to setup local images:');
        $this->info('   .\scripts\download-seed-images.ps1');
        $this->info('💡 Or use regular seeding with network images:');
        $this->info('   php artisan db:seed');

        return 1;
    }

    private function checkLocalImages(LocalImageProvider $provider): int
    {
        $this->info('🔍 Checking local seed images...');

        if ($provider->validateSeedImages()) {
            $this->info('✅ All local seed images are available.');

            return 0;
        }

        $this->error('❌ Some local seed images are missing.');

        return 1;
    }

    private function downloadMissingImages(LocalImageProvider $provider): int
    {
        $this->info('📥 This feature requires running the PowerShell script.');
        $this->info('💡 Run: .\scripts\download-seed-images.ps1');

        return 0;
    }
}

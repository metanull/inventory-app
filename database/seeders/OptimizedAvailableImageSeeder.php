<?php

namespace Database\Seeders;

use App\Models\AvailableImage;
use Illuminate\Database\Seeder;

class OptimizedAvailableImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating AvailableImage records with local images...');

        // Create all records using the factory but skip actual batch insert due to file operations
        // The factory needs to handle file copying, so we can't easily batch this
        AvailableImage::factory()->count(8)->create();
    }
}

<?php

namespace Database\Seeders;

use App\Models\ImageUpload;
use Illuminate\Database\Seeder;

class OptimizedImageUploadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating ImageUpload records with local images...');

        // Create all records using the factory but skip actual batch insert due to file operations
        // The factory needs to handle file copying, so we can't easily batch this
        ImageUpload::factory()->count(10)->create();
    }
}

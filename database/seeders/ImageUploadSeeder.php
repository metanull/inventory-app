<?php

namespace Database\Seeders;

use App\Models\ImageUpload;
use Illuminate\Database\Seeder;

class ImageUploadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ImageUpload::factory()->count(10)->create();
    }
}

<?php

namespace Database\Seeders;

use App\Models\AvailableImage;
use Illuminate\Database\Seeder;

class AvailableImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AvailableImage::factory()->count(8)->create();
    }
}

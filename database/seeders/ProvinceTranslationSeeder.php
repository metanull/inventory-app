<?php

namespace Database\Seeders;

use App\Models\ProvinceTranslation;
use Illuminate\Database\Seeder;

class ProvinceTranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ProvinceTranslation::factory()->count(10)->create();
    }
}

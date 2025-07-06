<?php

namespace Database\Seeders;

use App\Models\LocationTranslation;
use Illuminate\Database\Seeder;

class LocationTranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LocationTranslation::factory()->count(10)->create();
    }
}

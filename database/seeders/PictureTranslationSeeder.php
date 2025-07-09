<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PictureTranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\PictureTranslation::factory(50)->create();
    }
}

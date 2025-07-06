<?php

namespace Database\Seeders;

use App\Models\AddressTranslation;
use Illuminate\Database\Seeder;

class AddressTranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AddressTranslation::factory()->count(10)->create();
    }
}

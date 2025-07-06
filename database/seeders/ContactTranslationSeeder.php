<?php

namespace Database\Seeders;

use App\Models\ContactTranslation;
use Illuminate\Database\Seeder;

class ContactTranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ContactTranslation::factory()->count(10)->create();
    }
}

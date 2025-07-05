<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Language;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create contacts with language labels
        Contact::factory()
            ->count(10)
            ->create();
    }
}

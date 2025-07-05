<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
            UserSeeder::class,
            LanguageSeeder::class,
            CountrySeeder::class,
            ContactSeeder::class,
            ContextSeeder::class,
            ProjectSeeder::class,
            TagSeeder::class,
            PartnerSeeder::class,
            ItemSeeder::class,
            DetailSeeder::class,
            TagItemSeeder::class,
            PictureSeeder::class,
            ImageUploadSeeder::class,
            AvailableImageSeeder::class,
            ContextualizationSeeder::class,
            InternationalizationSeeder::class,
        ]);
    }
}

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
            ProvinceSeeder::class,
            LocationSeeder::class,
            AddressSeeder::class,
            ContextSeeder::class,
            ProjectSeeder::class,
            TagSeeder::class,
            PartnerSeeder::class,
            ItemSeeder::class,
            DetailSeeder::class,
            ItemTagSeeder::class,
            ItemTranslationSeeder::class,
            DetailTranslationSeeder::class,
            ImageUploadSeeder::class,
            AvailableImageSeeder::class,
            ArtistSeeder::class,
            AuthorSeeder::class,
            WorkshopSeeder::class,
            PictureSeeder::class,
            PictureTranslationSeeder::class,
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FastDatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with optimized seeders for faster performance.
     *
     * This seeder uses local images and batch operations to significantly reduce
     * seeding time while maintaining data quality.
     */
    public function run(): void
    {
        $this->command->info('🚀 Starting fast database seeding...');
        $startTime = microtime(true);

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
            CollectionSeeder::class,
            GallerySeeder::class,

            // Use optimized translation seeders
            OptimizedItemTranslationSeeder::class,
            OptimizedDetailTranslationSeeder::class,

            // Use optimized image seeders
            OptimizedImageUploadSeeder::class,
            OptimizedAvailableImageSeeder::class,
            ArtistSeeder::class,
            AuthorSeeder::class,
            WorkshopSeeder::class,
            OptimizedPictureSeeder::class,
            PictureTranslationSeeder::class,
            ExhibitionSeeder::class,
            ThemeSeeder::class,
        ]);

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $this->command->info("✅ Fast database seeding completed in {$duration} seconds!");
    }
}

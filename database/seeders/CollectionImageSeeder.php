<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\CollectionImage;
use Illuminate\Database\Seeder;

class CollectionImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get a sample of collections to attach images to
        $collections = Collection::inRandomOrder()->limit(5)->get();

        foreach ($collections as $collection) {
            // Create 2-4 images per collection with proper ordering
            $imageCount = rand(2, 4);
            for ($i = 1; $i <= $imageCount; $i++) {
                CollectionImage::factory()
                    ->forCollection($collection)
                    ->withOrder($i)
                    ->create();
            }
        }
    }
}

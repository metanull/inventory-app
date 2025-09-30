<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ItemImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = \App\Models\Item::all();

        if ($items->isEmpty()) {
            $this->command->warn('No items found. Please run ItemSeeder first.');

            return;
        }

        $this->command->info('Creating item images for '.$items->count().' items...');

        foreach ($items as $item) {
            // Create 1-4 images per item based on item type
            $imageCount = match ($item->type) {
                'object' => fake()->numberBetween(2, 4),
                'monument' => fake()->numberBetween(1, 3),
                'detail' => fake()->numberBetween(1, 2),
                'picture' => 1, // Picture items typically have just one main image
                default => fake()->numberBetween(1, 2),
            };

            for ($i = 1; $i <= $imageCount; $i++) {
                $factory = \App\Models\ItemImage::factory()
                    ->forItem($item)
                    ->withOrder($i);

                // Use specialized states based on item type
                match ($item->type) {
                    'object' => $factory->museumObject()->create(),
                    'monument' => $factory->monument()->create(),
                    default => $factory->create(),
                };
            }
        }

        $totalImages = \App\Models\ItemImage::count();
        $this->command->info("Created {$totalImages} item images successfully.");
    }
}

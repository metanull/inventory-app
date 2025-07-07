<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class ItemTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all items and tags
        $items = Item::all();
        $tags = Tag::all();

        // Skip if no items or tags exist
        if ($items->isEmpty() || $tags->isEmpty()) {
            return;
        }

        // Attach random tags to items
        $items->each(function (Item $item) use ($tags) {
            // Attach 1-3 random tags to each item
            $randomTags = $tags->random(min(3, $tags->count()));
            $item->tags()->attach($randomTags->pluck('id'));
        });
    }
}

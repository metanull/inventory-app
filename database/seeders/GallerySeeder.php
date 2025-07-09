<?php

namespace Database\Seeders;

use App\Models\Context;
use App\Models\Detail;
use App\Models\Gallery;
use App\Models\GalleryTranslation;
use App\Models\Item;
use App\Models\Language;
use App\Models\Partner;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get available languages and contexts
        $languages = Language::all();
        $contexts = Context::all();
        $partners = Partner::all();

        // Create 10 galleries
        Gallery::factory(10)->create()->each(function (Gallery $gallery) use ($languages, $contexts, $partners) {
            // Create translations for each language and context combination
            foreach ($languages->take(2) as $language) { // Limit to first 2 languages for performance
                foreach ($contexts->take(2) as $context) { // Limit to first 2 contexts for performance
                    GalleryTranslation::factory()
                        ->forGallery($gallery)
                        ->forLanguage($language)
                        ->forContext($context)
                        ->create();
                }
            }

            // Attach random partners with different levels
            if ($partners->isNotEmpty()) {
                $selectedPartners = $partners->random(rand(1, min(3, $partners->count())));
                foreach ($selectedPartners as $partner) {
                    $gallery->partners()->attach($partner->id, [
                        'level' => collect(['partner', 'associated_partner', 'minor_contributor'])->random(),
                        'backward_compatibility' => null,
                    ]);
                }
            }

            // Attach random items and details to the gallery
            $availableItems = Item::all();
            $availableDetails = Detail::all();

            if ($availableItems->isNotEmpty()) {
                $selectedItems = $availableItems->random(rand(1, min(5, $availableItems->count())));
                $order = 1;
                foreach ($selectedItems as $item) {
                    $gallery->items()->attach($item->id, [
                        'order' => $order++,
                        'backward_compatibility' => null,
                    ]);
                }
            }

            if ($availableDetails->isNotEmpty()) {
                $selectedDetails = $availableDetails->random(rand(1, min(3, $availableDetails->count())));
                $order = $gallery->items()->count() + 1; // Continue numbering after items
                foreach ($selectedDetails as $detail) {
                    $gallery->details()->attach($detail->id, [
                        'order' => $order++,
                        'backward_compatibility' => null,
                    ]);
                }
            }
        });
    }
}

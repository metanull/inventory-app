<?php

namespace Database\Seeders;

use App\Models\Context;
use App\Models\Item;
use App\Models\ItemTranslation;
use App\Models\Language;
use Illuminate\Database\Seeder;

class ItemTranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get default context and some languages
        $defaultContext = Context::where('is_default', true)->first();
        $englishLanguage = Language::where('id', 'eng')->first();
        $frenchLanguage = Language::where('id', 'fra')->first();

        if (! $defaultContext || ! $englishLanguage) {
            $this->command->warn('Default context or English language not found. Please run Context and Language seeders first.');

            return;
        }

        // Get some items to create translations for
        $items = Item::limit(10)->get();

        foreach ($items as $item) {
            // Create English translation in default context
            ItemTranslation::factory()->create([
                'item_id' => $item->id,
                'language_id' => $englishLanguage->id,
                'context_id' => $defaultContext->id,
                'name' => fake()->words(3, true),
                'description' => fake()->paragraph,
            ]);

            // Create French translation if available
            if ($frenchLanguage) {
                ItemTranslation::factory()->create([
                    'item_id' => $item->id,
                    'language_id' => $frenchLanguage->id,
                    'context_id' => $defaultContext->id,
                    'name' => fake('fr_FR')->words(3, true),
                    'description' => fake('fr_FR')->paragraph,
                ]);
            }
        }

        $this->command->info('Item translations seeded successfully.');
    }
}

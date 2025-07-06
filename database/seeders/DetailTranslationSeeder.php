<?php

namespace Database\Seeders;

use App\Models\Context;
use App\Models\Detail;
use App\Models\DetailTranslation;
use App\Models\Language;
use Illuminate\Database\Seeder;

class DetailTranslationSeeder extends Seeder
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

        // Get some details to create translations for
        $details = Detail::limit(10)->get();

        foreach ($details as $detail) {
            // Create English translation in default context
            DetailTranslation::factory()->create([
                'detail_id' => $detail->id,
                'language_id' => $englishLanguage->id,
                'context_id' => $defaultContext->id,
                'name' => fake()->words(3, true),
                'description' => fake()->paragraph,
            ]);

            // Create French translation if available
            if ($frenchLanguage) {
                DetailTranslation::factory()->create([
                    'detail_id' => $detail->id,
                    'language_id' => $frenchLanguage->id,
                    'context_id' => $defaultContext->id,
                    'name' => fake('fr_FR')->words(3, true),
                    'description' => fake('fr_FR')->paragraph,
                ]);
            }
        }

        $this->command->info('Detail translations seeded successfully.');
    }
}

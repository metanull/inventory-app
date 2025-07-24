<?php

namespace Database\Seeders;

use App\Models\Context;
use App\Models\Detail;
use App\Models\DetailTranslation;
use App\Models\Language;
use Illuminate\Database\Seeder;

class OptimizedDetailTranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating DetailTranslation records with batch operations...');

        // Get required references with single queries
        $defaultContext = Context::where('is_default', true)->first();
        $englishLanguage = Language::where('id', 'eng')->first();
        $frenchLanguage = Language::where('id', 'fra')->first();

        if (! $defaultContext || ! $englishLanguage) {
            $this->command->warn('Default context or English language not found. Please run Context and Language seeders first.');

            return;
        }

        // Get details in a single query
        $details = Detail::limit(10)->get();
        $translations = [];

        foreach ($details as $detail) {
            // Create English translation
            $translations[] = [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'detail_id' => $detail->id,
                'language_id' => $englishLanguage->id,
                'context_id' => $defaultContext->id,
                'name' => fake()->words(2, true),
                'description' => fake()->sentence,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Create French translation if available
            if ($frenchLanguage) {
                $translations[] = [
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'detail_id' => $detail->id,
                    'language_id' => $frenchLanguage->id,
                    'context_id' => $defaultContext->id,
                    'name' => fake('fr_FR')->words(2, true),
                    'description' => fake('fr_FR')->sentence,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insert all translations in a single batch operation
        if (! empty($translations)) {
            DetailTranslation::insert($translations);
        }
    }
}

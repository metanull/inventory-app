<?php

namespace Database\Seeders;

use App\Models\Contextualization;
use App\Models\Internationalization;
use App\Models\Language;
use Illuminate\Database\Seeder;

class InternationalizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have contextualizations and languages to work with
        if (Contextualization::count() === 0) {
            $this->call([
                ContextualizationSeeder::class,
            ]);
        }

        if (Language::count() === 0) {
            $this->call([
                LanguageSeeder::class,
            ]);
        }

        // Get some contextualizations and languages
        $contextualizations = Contextualization::limit(10)->get();
        $languages = Language::limit(5)->get();

        // Create internationalizations for each contextualization in different languages
        foreach ($contextualizations as $contextualization) {
            // Always create an English version
            if ($languages->where('id', 'eng')->isNotEmpty()) {
                Internationalization::factory()->create([
                    'contextualization_id' => $contextualization->id,
                    'language_id' => 'eng',
                ]);
            }

            // Create versions in other languages (randomly)
            $randomLanguages = $languages->where('id', '!=', 'eng')->random(rand(1, 2));
            foreach ($randomLanguages as $language) {
                Internationalization::factory()->create([
                    'contextualization_id' => $contextualization->id,
                    'language_id' => $language->id,
                ]);
            }
        }

        // Create additional random internationalizations
        Internationalization::factory(20)->create();
    }
}

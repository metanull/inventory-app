<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds for development and testing.
     *
     * Seeds a minimal, representative set of languages for fast loading.
     * Ensures exactly one language is marked as default (English).
     * For production data, use ProductionDataSeeder instead.
     */
    public function run(): void
    {
        // In production, recommend using ProductionDataSeeder
        if (app()->environment('production')) {
            $this->command->warn('Consider using ProductionDataSeeder for production environments to import the full language dataset.');
        }

        // Minimal representative set for development and testing
        $languages = [
            ['id' => 'eng', 'internal_name' => 'English', 'backward_compatibility' => 'en', 'is_default' => true],
            ['id' => 'fra', 'internal_name' => 'Français', 'backward_compatibility' => 'fr', 'is_default' => false],
            ['id' => 'deu', 'internal_name' => 'Deutsch', 'backward_compatibility' => 'de', 'is_default' => false],
            ['id' => 'spa', 'internal_name' => 'Español', 'backward_compatibility' => 'es', 'is_default' => false],
            ['id' => 'ita', 'internal_name' => 'Italiano', 'backward_compatibility' => 'it', 'is_default' => false],
            ['id' => 'jpn', 'internal_name' => 'Japanese', 'backward_compatibility' => 'ja', 'is_default' => false],
            ['id' => 'kor', 'internal_name' => 'Korean', 'backward_compatibility' => 'ko', 'is_default' => false],
            ['id' => 'ara', 'internal_name' => 'Arabic', 'backward_compatibility' => 'ar', 'is_default' => false],
            ['id' => 'hin', 'internal_name' => 'Hindi', 'backward_compatibility' => 'hi', 'is_default' => false],
            ['id' => 'nld', 'internal_name' => 'Dutch', 'backward_compatibility' => 'nl', 'is_default' => false],
            ['id' => 'por', 'internal_name' => 'Português', 'backward_compatibility' => 'pt', 'is_default' => false],
            ['id' => 'rus', 'internal_name' => 'Russian', 'backward_compatibility' => 'ru', 'is_default' => false],
        ];

        foreach ($languages as $language) {
            Language::create($language);
        }

        // Explicitly ensure English is the only default language
        $this->ensureSingleDefaultLanguage();
    }

    /**
     * Ensure exactly one language is marked as default.
     */
    private function ensureSingleDefaultLanguage(): void
    {
        $defaultLanguages = Language::where('is_default', true)->get();

        if ($defaultLanguages->count() === 1) {
            return;
        }

        // Reset all languages to non-default, then set English as default
        Language::query()->update(['is_default' => false]);

        $english = Language::find('eng');
        if ($english) {
            $english->update(['is_default' => true]);
        }
    }
}

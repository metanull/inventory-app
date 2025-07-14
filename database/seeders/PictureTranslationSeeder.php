<?php

namespace Database\Seeders;

use App\Models\Context;
use App\Models\Language;
use App\Models\Picture;
use App\Models\PictureTranslation;
use Illuminate\Database\Seeder;

class PictureTranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing data to avoid creating duplicates
        $pictures = Picture::all();
        $languages = Language::all();
        $contexts = Context::all();

        if ($pictures->isEmpty() || $languages->isEmpty() || $contexts->isEmpty()) {
            $this->command->warn('No pictures, languages, or contexts found. Please run the relevant seeders first.');

            return;
        }

        // Create picture translations using existing entities
        for ($i = 0; $i < 7; $i++) {
            $picture = $pictures->random();
            $language = $languages->random();
            $context = $contexts->random();

            // Check if this combination already exists
            $exists = PictureTranslation::where('picture_id', $picture->id)
                ->where('language_id', $language->id)
                ->where('context_id', $context->id)
                ->exists();

            if (! $exists) {
                PictureTranslation::factory()
                    ->forPicture($picture)
                    ->forLanguage($language)
                    ->forContext($context)
                    ->create();
            }
        }

        $this->command->info('Picture translations seeded successfully.');
    }
}

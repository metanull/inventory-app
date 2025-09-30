<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Theme;
use App\Models\ThemeTranslation;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    public function run(): void
    {
        $defaultContext = \App\Models\Context::where('is_default', true)->first();
        if (! $defaultContext) {
            return;
        }

        // Only create themes for exhibition-type collections
        Collection::where('type', 'exhibition')->get()->each(function ($collection) use ($defaultContext) {
            $mainThemes = Theme::factory()->count(2)->create([
                'collection_id' => $collection->id,
                'parent_id' => null,
            ]);
            $mainThemes->each(function ($theme) use ($defaultContext) {
                ThemeTranslation::factory()->create([
                    'theme_id' => $theme->id,
                    'language_id' => 'eng',
                    'context_id' => $defaultContext->id,
                ]);
                // TODO: Replace with ItemImage attachment when Theme-ItemImage relationship is implemented
                // Previously attached random pictures, but Picture model was removed in favor of ItemImage
                // Add subthemes
                Theme::factory()->count(2)->create([
                    'collection_id' => $theme->collection_id,
                    'parent_id' => $theme->id,
                ])->each(function ($subtheme) use ($defaultContext) {
                    ThemeTranslation::factory()->create([
                        'theme_id' => $subtheme->id,
                        'language_id' => 'eng',
                        'context_id' => $defaultContext->id,
                    ]);
                    // TODO: Replace with ItemImage attachment when Theme-ItemImage relationship is implemented
                    // Previously attached random pictures, but Picture model was removed in favor of ItemImage
                });
            });
        });
    }
}

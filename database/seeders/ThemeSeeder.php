<?php

namespace Database\Seeders;

use App\Models\Exhibition;
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
        Exhibition::all()->each(function ($exhibition) use ($defaultContext) {
            $mainThemes = Theme::factory()->count(2)->create([
                'exhibition_id' => $exhibition->id,
                'parent_id' => null,
            ]);
            $mainThemes->each(function ($theme) use ($defaultContext) {
                ThemeTranslation::factory()->create([
                    'theme_id' => $theme->id,
                    'language_id' => 'eng',
                    'context_id' => $defaultContext->id,
                ]);
                // Create standalone pictures for this theme (without direct polymorphic relationship)
                $pictures = \App\Models\Picture::factory()->standalone()->count(2)->create();
                $theme->pictures()->attach($pictures->pluck('id'));
                // Add subthemes
                Theme::factory()->count(2)->create([
                    'exhibition_id' => $theme->exhibition_id,
                    'parent_id' => $theme->id,
                ])->each(function ($subtheme) use ($defaultContext) {
                    ThemeTranslation::factory()->create([
                        'theme_id' => $subtheme->id,
                        'language_id' => 'eng',
                        'context_id' => $defaultContext->id,
                    ]);
                    // Create standalone pictures for this subtheme (without direct polymorphic relationship)
                    $pictures = \App\Models\Picture::factory()->standalone()->count(2)->create();
                    $subtheme->pictures()->attach($pictures->pluck('id'));
                });
            });
        });
    }
}

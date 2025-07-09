<?php

namespace Database\Seeders;

use App\Models\Exhibition;
use App\Models\ExhibitionTranslation;
use Illuminate\Database\Seeder;

class ExhibitionSeeder extends Seeder
{
    public function run(): void
    {
        $defaultContext = \App\Models\Context::where('is_default', true)->first();
        $defaultLanguage = 'eng'; // Use English as default for seeding

        Exhibition::factory()
            ->count(3)
            ->create()
            ->each(function ($exhibition) use ($defaultContext, $defaultLanguage) {
                ExhibitionTranslation::factory()->create([
                    'exhibition_id' => $exhibition->id,
                    'language_id' => $defaultLanguage,
                    'context_id' => $defaultContext->id,
                ]);
            });
    }
}

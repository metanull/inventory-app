<?php

namespace Database\Seeders;

use App\Models\Context;
use App\Models\Language;
use App\Models\Partner;
use App\Models\PartnerTranslation;
use Illuminate\Database\Seeder;

class PartnerTranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get default language and context
        $defaultLanguage = Language::default()->first();
        $defaultContext = Context::default()->first();

        // Get all partners
        $partners = Partner::all();

        foreach ($partners as $partner) {
            // Create default translation for each partner
            PartnerTranslation::factory()
                ->forPartner($partner->id)
                ->forLanguage($defaultLanguage->id)
                ->forContext($defaultContext->id)
                ->withFullAddress()
                ->withFullContact()
                ->create();

            // Optionally create additional translations for some partners
            if (fake()->boolean(30)) { // 30% chance
                $otherLanguage = Language::where('id', '!=', $defaultLanguage->id)->inRandomOrder()->first();
                if ($otherLanguage) {
                    PartnerTranslation::factory()
                        ->forPartner($partner->id)
                        ->forLanguage($otherLanguage->id)
                        ->forContext($defaultContext->id)
                        ->withFullAddress()
                        ->create();
                }
            }
        }
    }
}

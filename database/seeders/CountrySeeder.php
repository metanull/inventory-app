<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds for development and testing.
     *
     * Seeds a minimal, representative set of countries for fast loading.
     * For production data, use ProductionDataSeeder instead.
     */
    public function run(): void
    {
        // In production, recommend using ProductionDataSeeder
        if (app()->environment('production')) {
            $this->command->warn('Consider using ProductionDataSeeder for production environments to import the full country dataset.');
        }

        // Minimal representative set for development and testing
        $countries = [
            ['id' => 'usa', 'internal_name' => 'United States of America', 'backward_compatibility' => 'us'],
            ['id' => 'can', 'internal_name' => 'Canada', 'backward_compatibility' => 'ca'],
            ['id' => 'gbr', 'internal_name' => 'United Kingdom of Great Britain and Northern Ireland', 'backward_compatibility' => 'gb'],
            ['id' => 'fra', 'internal_name' => 'France', 'backward_compatibility' => 'fr'],
            ['id' => 'deu', 'internal_name' => 'Germany', 'backward_compatibility' => 'de'],
            ['id' => 'ita', 'internal_name' => 'Italy', 'backward_compatibility' => 'it'],
            ['id' => 'esp', 'internal_name' => 'Spain', 'backward_compatibility' => 'es'],
            ['id' => 'jpn', 'internal_name' => 'Japan', 'backward_compatibility' => 'jp'],
            ['id' => 'chn', 'internal_name' => 'China', 'backward_compatibility' => 'cn'],
            ['id' => 'ind', 'internal_name' => 'India', 'backward_compatibility' => 'in'],
            ['id' => 'bra', 'internal_name' => 'Brazil', 'backward_compatibility' => 'br'],
            ['id' => 'aus', 'internal_name' => 'Australia', 'backward_compatibility' => 'au'],
        ];

        foreach ($countries as $country) {
            Country::create($country);
        }
    }
}

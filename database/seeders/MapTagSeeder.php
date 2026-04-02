<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class MapTagSeeder extends Seeder
{
    /**
     * Seed the 'map' image-type tag.
     */
    public function run(): void
    {
        Tag::firstOrCreate(
            [
                'internal_name' => 'map',
                'category' => 'image-type',
            ],
            [
                'language_id' => null,
                'description' => 'Map image',
            ]
        );
    }
}

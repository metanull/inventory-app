<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class MapTagSeeder extends Seeder
{
    /**
     * The importer's TagHelper builds tag keys as
     * mwnf3:tags:{category}:{language}:{name} and looks tags up by that key.
     * Without it the seeded tag was unreachable from the importer, and map
     * images were silently never tagged.
     */
    private const BACKWARD_COMPATIBILITY = 'mwnf3:tags:image-type:eng:map';

    /**
     * Seed the 'map' image-type tag.
     */
    public function run(): void
    {
        $tag = Tag::firstOrCreate(
            [
                'internal_name' => 'map',
                'category' => 'image-type',
            ],
            [
                'language_id' => null,
                'description' => 'Map image',
                'backward_compatibility' => self::BACKWARD_COMPATIBILITY,
            ]
        );

        // Repair a tag seeded before it carried a key, without disturbing one
        // the importer created (which already has the right key) or the
        // language and description of either.
        if ($tag->backward_compatibility === null) {
            $tag->update(['backward_compatibility' => self::BACKWARD_COMPATIBILITY]);
        }
    }
}

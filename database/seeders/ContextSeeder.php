<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Context;

class ContextSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contexts = [
            ['backward_compatibility' => 'AMA', 'internal_name' => 'Medieval Art', ], 
            ['backward_compatibility' => 'AMT', 'internal_name' => 'Baroque Art', ], 
            ['backward_compatibility' => 'AMU', 'internal_name' => 'Amulets and Talismans', ], 
            ['backward_compatibility' => 'ARC', 'internal_name' => 'Archaeological Objects', ], 
            ['backward_compatibility' => 'ARH', 'internal_name' => 'Architectural elements', ], 
            ['backward_compatibility' => 'ARM', 'internal_name' => 'Arms and Armoury', ], 
            ['backward_compatibility' => 'BAR', 'internal_name' => 'Discover Baroque Art', ], 
            ['backward_compatibility' => 'CAL', 'internal_name' => 'Calligraphy', ], 
            ['backward_compatibility' => 'CAR', 'internal_name' => 'Cartoons and Caricatures', ], 
            ['backward_compatibility' => 'CER', 'internal_name' => 'Ceramics', ], 
            ['backward_compatibility' => 'CLO', 'internal_name' => 'Clothing and Costume', ], 
            ['backward_compatibility' => 'COI', 'internal_name' => 'Coins and Medals (87)', ], 
            ['backward_compatibility' => 'COM', 'internal_name' => 'Communication and Transportation', ], 
            ['backward_compatibility' => 'CUR', 'internal_name' => 'Curiosities', ], 
            ['backward_compatibility' => 'DCA', 'internal_name' => 'Discover Carpet Art', ], 
            ['backward_compatibility' => 'DGA', 'internal_name' => 'Discover Glass Art', ], 
            ['backward_compatibility' => 'EPM', 'internal_name' => 'Explore Islamic Art Collections', ], 
            ['backward_compatibility' => 'EXAID', 'internal_name' => 'Arts in Dialogue', ], 
            ['backward_compatibility' => 'EXC', 'internal_name' => 'Excluded', ], 
            ['backward_compatibility' => 'EXHCOLOUR', 'internal_name' => 'The Use Of Colours In Art', ], 
            ['backward_compatibility' => 'EXTEST', 'internal_name' => 'Test Exhibition', ], 
            ['backward_compatibility' => 'EXTHE', 'internal_name' => 'The Table Is Set', ], 
            ['backward_compatibility' => 'EXWIT', 'internal_name' => 'With Brush and Qalam', ], 
            ['backward_compatibility' => 'FUN', 'internal_name' => 'Funerary Objects', ], 
            ['backward_compatibility' => 'GalEx5', 'internal_name' => 'Lost Memories Along the Hijaz Railway: From Istanbul to Mecca', ], 
            ['backward_compatibility' => 'GALLERIES', 'internal_name' => 'MWNF Galleries', ], 
            ['backward_compatibility' => 'GOL', 'internal_name' => 'Gold and Silver', ], 
            ['backward_compatibility' => 'GPA', 'internal_name' => 'The Great Patrons of the Arts', ], 
            ['backward_compatibility' => 'HCA', 'internal_name' => 'Historical Cars', ], 
            ['backward_compatibility' => 'IAM', 'internal_name' => 'Islamic Art in the Mediterranean', ], 
            ['backward_compatibility' => 'ISL', 'internal_name' => 'Discover Islamic Art', ], 
            ['backward_compatibility' => 'IVO', 'internal_name' => 'Ivory', ], 
            ['backward_compatibility' => 'JEW', 'internal_name' => 'Jewellery', ], 
            ['backward_compatibility' => 'LAN', 'internal_name' => 'Landscapes', ], 
            ['backward_compatibility' => 'LEA', 'internal_name' => 'Leatherworks', ], 
            ['backward_compatibility' => 'MAN', 'internal_name' => 'Manuscripts', ], 
            ['backward_compatibility' => 'MET', 'internal_name' => 'Metalwork', ], 
            ['backward_compatibility' => 'MOS', 'internal_name' => 'Mosaics', ], 
            ['backward_compatibility' => 'MUS', 'internal_name' => 'Musical Instruments', ], 
            ['backward_compatibility' => 'PAI', 'internal_name' => 'Paintings', ], 
            ['backward_compatibility' => 'PHO', 'internal_name' => 'Photographs', ], 
            ['backward_compatibility' => 'POR', 'internal_name' => 'Porcelain', ], 
            ['backward_compatibility' => 'POT', 'internal_name' => 'Portraits', ], 
            ['backward_compatibility' => 'PRI', 'internal_name' => 'Prints and Drawings', ], 
            ['backward_compatibility' => 'PRS', 'internal_name' => 'Precious Stones', ], 
            ['backward_compatibility' => 'REL', 'internal_name' => 'Religious Life', ], 
            ['backward_compatibility' => 'SCI', 'internal_name' => 'Scientific Objects', ], 
            ['backward_compatibility' => 'SCU', 'internal_name' => 'Sculptures', ], 
            ['backward_compatibility' => 'STI', 'internal_name' => 'Still life', ], 
            ['backward_compatibility' => 'TEX', 'internal_name' => 'Textiles', ], 
            ['backward_compatibility' => 'THE', 'internal_name' => 'Theatre', ], 
            ['backward_compatibility' => 'TOY', 'internal_name' => 'Toys and Games', ], 
            ['backward_compatibility' => 'UNC', 'internal_name' => 'Unclear', ], 
            ['backward_compatibility' => 'WAL', 'internal_name' => 'Wall Paintings and Frescoes', ], 
            ['backward_compatibility' => 'WEI', 'internal_name' => 'Weights and Measures', ], 
            ['backward_compatibility' => 'WOO', 'internal_name' => 'Furniture and Woodwork', ], 
            ['backward_compatibility' => 'AWE', 'internal_name' => 'Sharing History', ], 
            ['backward_compatibility' => 'RUS', 'internal_name' => 'The Book of Ways and States', ], 
            ['backward_compatibility' => 'USA', 'internal_name' => 'Religious Images', ], 
            ['backward_compatibility' => 'EXH', 'internal_name' => 'Exhibitions', ], 
            ['backward_compatibility' => 'THG', 'internal_name' => 'Thematic Galleries', ], 
        ];

        foreach ($contexts as $context) {
            Context::create($context);
        }
    }
}

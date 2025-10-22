<?php

namespace Database\Seeders;

use App\Models\Glossary;
use App\Models\GlossarySpelling;
use App\Models\GlossaryTranslation;
use App\Models\Language;
use Illuminate\Database\Seeder;

class GlossarySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get English and French languages
        $eng = Language::find('eng');
        $fra = Language::find('fra');

        // Example glossary entries
        $examples = [
            [
                'internal_name' => 'manuscript',
                'spellings' => [
                    'eng' => ['manuscript', 'manuscrit', 'MS'],
                    'fra' => ['manuscrit'],
                ],
                'definitions' => [
                    'eng' => 'A handwritten or hand-copied document, especially one of historical or literary significance.',
                    'fra' => 'Un document écrit ou copié à la main, en particulier de signification historique ou littéraire.',
                ],
            ],
            [
                'internal_name' => 'calligraphy',
                'spellings' => [
                    'eng' => ['calligraphy', 'calligraphic'],
                    'fra' => ['calligraphie'],
                ],
                'definitions' => [
                    'eng' => 'Decorative handwriting or handwritten lettering.',
                    'fra' => 'Écriture décorative ou lettrage manuscrit.',
                ],
            ],
            [
                'internal_name' => 'miniature',
                'spellings' => [
                    'eng' => ['miniature', 'illumination'],
                    'fra' => ['miniature', 'enluminure'],
                ],
                'definitions' => [
                    'eng' => 'A small, detailed painting or illustration, often found in manuscripts.',
                    'fra' => 'Une petite peinture ou illustration détaillée, souvent trouvée dans les manuscrits.',
                ],
            ],
        ];

        foreach ($examples as $example) {
            $glossary = Glossary::create([
                'internal_name' => $example['internal_name'],
            ]);

            // Create spellings
            if (isset($example['spellings'])) {
                foreach ($example['spellings'] as $langId => $spellings) {
                    foreach ($spellings as $spelling) {
                        GlossarySpelling::create([
                            'glossary_id' => $glossary->id,
                            'language_id' => $langId,
                            'spelling' => $spelling,
                        ]);
                    }
                }
            }

            // Create translations/definitions
            if (isset($example['definitions'])) {
                foreach ($example['definitions'] as $langId => $definition) {
                    GlossaryTranslation::create([
                        'glossary_id' => $glossary->id,
                        'language_id' => $langId,
                        'definition' => $definition,
                    ]);
                }
            }
        }
    }
}

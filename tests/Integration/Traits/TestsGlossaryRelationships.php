<?php

namespace Tests\Integration\Traits;

use App\Jobs\SyncItemTranslationSpellings;
use App\Jobs\SyncSpellingToItemTranslations;
use App\Models\Glossary;
use App\Models\GlossarySpelling;
use App\Models\ItemTranslation;
use App\Models\Language;
use Illuminate\Support\Collection;

/**
 * Provides reusable helper methods for glossary relationship tests.
 *
 * This trait centralizes common patterns for creating glossaries, spellings,
 * item translations, and testing their relationships in integration tests.
 */
trait TestsGlossaryRelationships
{
    /**
     * Create a glossary with multiple spellings.
     *
     * @param  int  $count  Number of spellings to create
     * @param  array  $glossaryAttributes  Additional attributes for the glossary
     * @param  array  $spellingAttributes  Base attributes for the spellings
     */
    protected function createGlossaryWithSpellings(
        int $count = 3,
        array $glossaryAttributes = [],
        array $spellingAttributes = []
    ): Glossary {
        $glossary = Glossary::factory()->create($glossaryAttributes);
        $language = Language::factory()->create();

        $defaultSpellings = ['pottery', 'ceramic', 'artifact'];

        for ($i = 0; $i < $count; $i++) {
            GlossarySpelling::factory()->create(array_merge([
                'glossary_id' => $glossary->id,
                'language_id' => $language->id,
                'spelling' => $defaultSpellings[$i % count($defaultSpellings)].' '.($i + 1),
            ], $spellingAttributes));
        }

        return $glossary->fresh(['spellings']);
    }

    /**
     * Create an item translation linked to a specific glossary spelling.
     *
     * @param  GlossarySpelling  $spelling  The spelling to link to
     * @param  array  $attributes  Additional attributes for the item translation
     */
    protected function createLinkedItemTranslation(
        GlossarySpelling $spelling,
        array $attributes = []
    ): ItemTranslation {
        $translation = ItemTranslation::factory()->create(array_merge([
            'language_id' => $spelling->language_id,
            'name' => 'Item with '.$spelling->spelling,
        ], $attributes));

        // Manually attach the spelling
        $translation->spellings()->attach($spelling->id);

        return $translation->fresh(['spellings']);
    }

    /**
     * Create multiple item translations that match a spelling pattern.
     *
     * @param  string  $spellingText  The text pattern to match in translation names
     * @param  Language  $language  The language for the translations
     * @param  int  $count  Number of translations to create
     */
    protected function createItemTranslationsWithPattern(
        string $spellingText,
        Language $language,
        int $count = 3
    ): Collection {
        $translations = collect();

        for ($i = 0; $i < $count; $i++) {
            $translations->push(ItemTranslation::factory()->create([
                'language_id' => $language->id,
                'name' => ucfirst($spellingText).' Item '.($i + 1),
            ]));
        }

        return $translations;
    }

    /**
     * Create a spelling with multiple linked item translations.
     *
     * @param  string  $spellingText  The spelling text
     * @param  int  $translationCount  Number of item translations to link
     * @param  array  $spellingAttributes  Additional spelling attributes
     */
    protected function createSpellingWithLinkedTranslations(
        string $spellingText,
        int $translationCount = 3,
        array $spellingAttributes = []
    ): GlossarySpelling {
        $language = Language::factory()->create();

        $spelling = GlossarySpelling::factory()->create(array_merge([
            'language_id' => $language->id,
            'spelling' => $spellingText,
        ], $spellingAttributes));

        for ($i = 0; $i < $translationCount; $i++) {
            $translation = ItemTranslation::factory()->create([
                'language_id' => $language->id,
                'name' => ucfirst($spellingText).' Item '.($i + 1),
            ]);
            $translation->spellings()->attach($spelling->id);
        }

        return $spelling->fresh(['itemTranslations']);
    }

    /**
     * Assert that spelling links are intact between a spelling and translations.
     *
     * @param  GlossarySpelling  $spelling  The spelling to check
     * @param  array|Collection  $translations  The translations that should be linked
     */
    protected function assertSpellingLinksIntact(GlossarySpelling $spelling, array|Collection $translations): void
    {
        $spelling = $spelling->fresh(['itemTranslations']);
        $linkedTranslationIds = $spelling->itemTranslations->pluck('id')->toArray();

        if ($translations instanceof Collection) {
            $translations = $translations->all();
        }

        foreach ($translations as $translation) {
            $this->assertContains(
                $translation->id,
                $linkedTranslationIds,
                "Translation {$translation->id} should be linked to spelling {$spelling->id}"
            );

            $this->assertDatabaseHas('item_translation_spelling', [
                'item_translation_id' => $translation->id,
                'spelling_id' => $spelling->id,
            ]);
        }
    }

    /**
     * Assert that spelling links have been removed between a spelling and translations.
     *
     * @param  string|int  $spellingId  The spelling ID to check
     * @param  array|Collection  $translations  The translations that should NOT be linked
     */
    protected function assertSpellingLinksRemoved(string|int $spellingId, array|Collection $translations): void
    {
        if ($translations instanceof Collection) {
            $translations = $translations->all();
        }

        foreach ($translations as $translation) {
            $this->assertDatabaseMissing('item_translation_spelling', [
                'item_translation_id' => $translation->id,
                'spelling_id' => $spellingId,
            ]);
        }
    }

    /**
     * Assert that an item translation contains specific spellings.
     *
     * @param  ItemTranslation  $translation  The translation to check
     * @param  array|Collection  $spellings  The spellings that should be linked
     */
    protected function assertTranslationHasSpellings(ItemTranslation $translation, array|Collection $spellings): void
    {
        $translation = $translation->fresh(['spellings']);

        if ($spellings instanceof Collection) {
            $spellings = $spellings->all();
        }

        foreach ($spellings as $spelling) {
            $this->assertTrue(
                $translation->spellings->contains('id', $spelling->id),
                "Translation {$translation->id} should contain spelling {$spelling->id}"
            );
        }
    }

    /**
     * Assert that an item translation does not contain specific spellings.
     *
     * @param  ItemTranslation  $translation  The translation to check
     * @param  array|Collection  $spellings  The spellings that should NOT be linked
     */
    protected function assertTranslationDoesNotHaveSpellings(ItemTranslation $translation, array|Collection $spellings): void
    {
        $translation = $translation->fresh(['spellings']);

        if ($spellings instanceof Collection) {
            $spellings = $spellings->all();
        }

        foreach ($spellings as $spelling) {
            $this->assertFalse(
                $translation->spellings->contains('id', $spelling->id),
                "Translation {$translation->id} should NOT contain spelling {$spelling->id}"
            );
        }
    }

    /**
     * Manually dispatch a sync job for an item translation.
     *
     * @param  ItemTranslation  $translation  The translation to sync
     */
    protected function syncItemTranslationSpellings(ItemTranslation $translation): void
    {
        $job = new SyncItemTranslationSpellings($translation->id);
        $job->handle();
    }

    /**
     * Manually dispatch a sync job for a spelling.
     *
     * @param  GlossarySpelling  $spelling  The spelling to sync
     */
    protected function syncSpellingToItemTranslations(GlossarySpelling $spelling): void
    {
        $job = new SyncSpellingToItemTranslations($spelling->id);
        $job->handle();
    }

    /**
     * Create a language for glossary tests.
     *
     * @param  array  $attributes  Additional attributes for the language
     */
    protected function createLanguageForGlossary(array $attributes = []): Language
    {
        return Language::factory()->create($attributes);
    }

    /**
     * Create multiple spellings with a common language.
     *
     * @param  array  $spellingTexts  Array of spelling texts to create
     * @param  Language|null  $language  The language to use (creates one if null)
     * @param  array  $baseAttributes  Base attributes for all spellings
     */
    protected function createSpellings(
        array $spellingTexts,
        ?Language $language = null,
        array $baseAttributes = []
    ): Collection {
        $language = $language ?? Language::factory()->create();

        return collect($spellingTexts)->map(function ($text) use ($language, $baseAttributes) {
            return GlossarySpelling::factory()->create(array_merge([
                'language_id' => $language->id,
                'spelling' => $text,
            ], $baseAttributes));
        });
    }

    /**
     * Assert that all spelling links for a translation have been removed.
     *
     * @param  ItemTranslation|int|string  $translation  The translation or its ID
     */
    protected function assertAllSpellingLinksRemoved(ItemTranslation|int|string $translation): void
    {
        $translationId = $translation instanceof ItemTranslation ? $translation->id : $translation;

        $this->assertDatabaseMissing('item_translation_spelling', [
            'item_translation_id' => $translationId,
        ]);
    }

    /**
     * Assert that all item translation links for a spelling have been removed.
     *
     * @param  GlossarySpelling|int|string  $spelling  The spelling or its ID
     */
    protected function assertAllItemTranslationLinksRemoved(GlossarySpelling|int|string $spelling): void
    {
        $spellingId = $spelling instanceof GlossarySpelling ? $spelling->id : $spelling;

        $this->assertDatabaseMissing('item_translation_spelling', [
            'spelling_id' => $spellingId,
        ]);
    }
}

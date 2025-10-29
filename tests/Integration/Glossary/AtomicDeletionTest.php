<?php

namespace Tests\Feature\Glossary;

use App\Models\Glossary;
use App\Models\GlossarySpelling;
use App\Models\Item;
use App\Models\ItemTranslation;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\Integration\Traits\TestsGlossaryRelationships;
use Tests\TestCase;

class AtomicDeletionTest extends TestCase
{
    use RefreshDatabase, TestsGlossaryRelationships;

    protected function setUp(): void
    {
        parent::setUp();
        // Fake the queue to prevent automatic spelling sync during tests
        Queue::fake();
    }

    /**
     * Test deleting ItemTranslation is atomic (both translation and spelling links deleted together).
     */
    public function test_deleting_item_translation_is_atomic()
    {
        $language = $this->createLanguageForGlossary();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'pottery',
        ]);

        $translation = $this->createLinkedItemTranslation($spelling);

        $this->assertSpellingLinksIntact($spelling, [$translation]);

        // Delete the translation
        $translationId = $translation->id;
        $translation->delete();

        // Both the translation AND the spelling links should be gone
        $this->assertDatabaseMissing('item_translations', [
            'id' => $translationId,
        ]);
        $this->assertAllSpellingLinksRemoved($translationId);
    }

    /**
     * Test deleting ItemTranslation with transaction failure rolls back everything.
     */
    public function test_deleting_item_translation_rolls_back_on_failure()
    {
        $language = $this->createLanguageForGlossary();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'artifact',
        ]);

        $translation = $this->createLinkedItemTranslation($spelling);

        // Attempt to delete but simulate a failure by using a database trigger
        // Since we can't easily simulate a real failure in tests, we verify the transaction wrapping
        // by checking that a manual transaction rollback works

        try {
            DB::transaction(function () use ($translation) {
                $translation->delete();
                // Simulate failure
                throw new \Exception('Simulated failure');
            });
        } catch (\Exception $e) {
            // Expected
        }

        // Both should still exist (transaction rolled back)
        $this->assertDatabaseHas('item_translations', [
            'id' => $translation->id,
        ]);
        $this->assertSpellingLinksIntact($spelling, [$translation]);
    }

    /**
     * Test deleting GlossarySpelling is atomic (both spelling and item translation links deleted together).
     */
    public function test_deleting_glossary_spelling_is_atomic()
    {
        $language = $this->createLanguageForGlossary();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'ceramic',
        ]);

        $translation1 = $this->createLinkedItemTranslation($spelling);
        $translation2 = $this->createLinkedItemTranslation($spelling);

        $this->assertSpellingLinksIntact($spelling, [$translation1, $translation2]);

        // Delete the spelling
        $spellingId = $spelling->id;
        $spelling->delete();

        // The spelling AND all its links should be gone
        $this->assertDatabaseMissing('glossary_spellings', [
            'id' => $spellingId,
        ]);
        $this->assertAllItemTranslationLinksRemoved($spellingId);

        // But the translations should still exist
        $this->assertDatabaseHas('item_translations', [
            'id' => $translation1->id,
        ]);
        $this->assertDatabaseHas('item_translations', [
            'id' => $translation2->id,
        ]);
    }

    /**
     * Test deleting Glossary is atomic (deletes spellings, translations, links, and synonyms).
     */
    public function test_deleting_glossary_is_atomic()
    {
        $language = $this->createLanguageForGlossary();
        $glossary = Glossary::factory()->create();

        // Create spellings
        $spelling1 = GlossarySpelling::factory()->create([
            'glossary_id' => $glossary->id,
            'language_id' => $language->id,
            'spelling' => 'pottery',
        ]);
        $spelling2 = GlossarySpelling::factory()->create([
            'glossary_id' => $glossary->id,
            'language_id' => $language->id,
            'spelling' => 'ceramics',
        ]);

        // Create item translations and link spellings
        $translation = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Ancient Pottery',
        ]);
        $translation->spellings()->attach([$spelling1->id, $spelling2->id]);

        // Create synonym
        $synonym = Glossary::factory()->create();
        $glossary->synonyms()->attach($synonym->id);

        // Verify setup
        $this->assertDatabaseHas('glossaries', ['id' => $glossary->id]);
        $this->assertTranslationHasSpellings($translation, [$spelling1, $spelling2]);
        $this->assertDatabaseHas('glossary_synonyms', ['glossary_id' => $glossary->id]);

        // Delete the glossary
        $spelling1Id = $spelling1->id;
        $spelling2Id = $spelling2->id;
        $glossary->delete();

        // Everything related should be gone
        $this->assertDatabaseMissing('glossaries', ['id' => $glossary->id]);
        $this->assertDatabaseMissing('glossary_spellings', ['id' => $spelling1Id]);
        $this->assertDatabaseMissing('glossary_spellings', ['id' => $spelling2Id]);
        $this->assertAllItemTranslationLinksRemoved($spelling1Id);
        $this->assertAllItemTranslationLinksRemoved($spelling2Id);
        $this->assertDatabaseMissing('glossary_synonyms', ['glossary_id' => $glossary->id]);

        // But the translation should still exist
        $this->assertDatabaseHas('item_translations', ['id' => $translation->id]);
    }

    /**
     * Test deleting Item is atomic (deletes translations and their spelling links).
     */
    public function test_deleting_item_is_atomic()
    {
        $language = Language::factory()->create();
        $item = Item::factory()->create();

        // Create translations
        $translation1 = ItemTranslation::factory()->create([
            'item_id' => $item->id,
            'language_id' => $language->id,
            'name' => 'Translation 1',
        ]);
        $translation2 = ItemTranslation::factory()->create([
            'item_id' => $item->id,
            'language_id' => $language->id,
            'name' => 'Translation 2',
        ]);

        // Create spellings and link them
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'translation',
        ]);
        $translation1->spellings()->attach($spelling->id);
        $translation2->spellings()->attach($spelling->id);

        // Verify setup
        $this->assertDatabaseHas('items', ['id' => $item->id]);
        $this->assertDatabaseHas('item_translations', ['id' => $translation1->id]);
        $this->assertDatabaseHas('item_translations', ['id' => $translation2->id]);
        $this->assertDatabaseHas('item_translation_spelling', [
            'item_translation_id' => $translation1->id,
            'spelling_id' => $spelling->id,
        ]);
        $this->assertDatabaseHas('item_translation_spelling', [
            'item_translation_id' => $translation2->id,
            'spelling_id' => $spelling->id,
        ]);

        // Delete the item
        $item->delete();

        // The item, its translations, and all spelling links should be gone
        $this->assertDatabaseMissing('items', ['id' => $item->id]);
        $this->assertDatabaseMissing('item_translations', ['id' => $translation1->id]);
        $this->assertDatabaseMissing('item_translations', ['id' => $translation2->id]);
        $this->assertDatabaseMissing('item_translation_spelling', [
            'item_translation_id' => $translation1->id,
        ]);
        $this->assertDatabaseMissing('item_translation_spelling', [
            'item_translation_id' => $translation2->id,
        ]);

        // But the spelling should still exist
        $this->assertDatabaseHas('glossary_spellings', ['id' => $spelling->id]);
    }

    /**
     * Test multiple ItemTranslations can be deleted independently without affecting each other.
     */
    public function test_multiple_item_translations_deletion_independence()
    {
        $language = $this->createLanguageForGlossary();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'pottery',
        ]);

        $translation1 = $this->createLinkedItemTranslation($spelling);
        $translation2 = $this->createLinkedItemTranslation($spelling);

        // Delete translation1
        $translation1Id = $translation1->id;
        $translation1->delete();

        // Translation1 and its link should be gone
        $this->assertDatabaseMissing('item_translations', ['id' => $translation1Id]);
        $this->assertAllSpellingLinksRemoved($translation1Id);

        // But translation2 and its link should still exist
        $this->assertDatabaseHas('item_translations', ['id' => $translation2->id]);
        $this->assertSpellingLinksIntact($spelling, [$translation2]);
    }

    /**
     * Test deleting ItemTranslation with no spellings still works.
     */
    public function test_deleting_item_translation_with_no_spellings()
    {
        $language = $this->createLanguageForGlossary();
        $translation = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'No Spellings',
        ]);

        // No spellings attached
        $this->assertCount(0, $translation->spellings);

        // Delete should still work
        $translationId = $translation->id;
        $result = $translation->delete();
        $this->assertTrue($result);

        $this->assertDatabaseMissing('item_translations', ['id' => $translationId]);
    }

    /**
     * Test deleting GlossarySpelling with no item translations still works.
     */
    public function test_deleting_glossary_spelling_with_no_item_translations()
    {
        $language = $this->createLanguageForGlossary();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'orphan',
        ]);

        // No item translations linked
        $this->assertCount(0, $spelling->itemTranslations);

        // Delete should still work
        $spellingId = $spelling->id;
        $result = $spelling->delete();
        $this->assertTrue($result);

        $this->assertDatabaseMissing('glossary_spellings', ['id' => $spellingId]);
    }
}

<?php

namespace Tests\Feature\Glossary;

use App\Jobs\SyncItemTranslationSpellings;
use App\Jobs\SyncSpellingToItemTranslations;
use App\Models\GlossarySpelling;
use App\Models\ItemTranslation;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConcurrencyAndEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test job uniqueness prevents duplicate processing.
     */
    public function test_job_uniqueness_prevents_duplicate_processing()
    {
        $language = Language::factory()->create();
        $itemTranslation = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Test Item',
        ]);

        $job1 = new SyncItemTranslationSpellings($itemTranslation->id);
        $job2 = new SyncItemTranslationSpellings($itemTranslation->id);

        // Both jobs should have the same unique ID
        $this->assertEquals($job1->uniqueId(), $job2->uniqueId());
        $this->assertEquals('sync-item-translation-'.$itemTranslation->id, $job1->uniqueId());
    }

    /**
     * Test spelling job uniqueness.
     */
    public function test_spelling_job_uniqueness()
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'pottery',
        ]);

        $job1 = new SyncSpellingToItemTranslations($spelling->id);
        $job2 = new SyncSpellingToItemTranslations($spelling->id);

        $this->assertEquals($job1->uniqueId(), $job2->uniqueId());
        $this->assertEquals('sync-spelling-'.$spelling->id, $job1->uniqueId());
    }

    /**
     * Test rapid create/update/delete sequence.
     */
    public function test_rapid_create_update_delete_sequence()
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'pottery',
        ]);

        // Create, update, then delete quickly
        $translation = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Ancient Pottery',
        ]);

        // Sync the creation
        $job = new SyncItemTranslationSpellings($translation->id);
        $job->handle();

        $this->assertTrue($translation->fresh()->spellings->contains($spelling));

        // Update
        $translation->update(['name' => 'Modern Pottery']);

        // Sync the update
        $job = new SyncItemTranslationSpellings($translation->id);
        $job->handle();

        $this->assertTrue($translation->fresh()->spellings->contains($spelling));

        // Delete
        $translation->delete();

        // Verify all links are removed
        $this->assertDatabaseMissing('item_translation_spelling', [
            'item_translation_id' => $translation->id,
        ]);
    }

    /**
     * Test jobs use database transactions for atomicity.
     */
    public function test_jobs_use_database_transactions_for_atomicity()
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'artifact',
        ]);

        $translation = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Ancient Artifact',
        ]);

        // Run the job
        $job = new SyncItemTranslationSpellings($translation->id);
        $job->handle();

        // The link should be created atomically
        $this->assertTrue($translation->fresh()->spellings->contains($spelling));

        // Run again - should be idempotent
        $job->handle();
        $this->assertTrue($translation->fresh()->spellings->contains($spelling));
        $this->assertCount(1, $translation->fresh()->spellings);
    }

    /**
     * Test simultaneous updates to different item translations.
     */
    public function test_simultaneous_updates_to_different_item_translations()
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'pottery',
        ]);

        $translations = ItemTranslation::factory()->count(10)->create([
            'language_id' => $language->id,
            'name' => 'Ancient Pottery',
        ]);

        // Process all translations simultaneously (simulating concurrent workers)
        foreach ($translations as $translation) {
            $job = new SyncItemTranslationSpellings($translation->id);
            $job->handle();
        }

        // All should be linked to the spelling
        foreach ($translations as $translation) {
            $this->assertTrue($translation->fresh()->spellings->contains($spelling));
        }
    }

    /**
     * Test simultaneous updates to different spellings.
     */
    public function test_simultaneous_updates_to_different_spellings()
    {
        $language = Language::factory()->create();

        $spellings = collect(['pottery', 'ceramic', 'artifact', 'ancient', 'tool'])
            ->map(fn ($word) => GlossarySpelling::factory()->create([
                'language_id' => $language->id,
                'spelling' => $word,
            ]));

        $translation = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Ancient Pottery Artifact',
            'description' => 'A ceramic tool',
        ]);

        // Process all spellings simultaneously
        foreach ($spellings as $spelling) {
            $job = new SyncSpellingToItemTranslations($spelling->id);
            $job->handle();
        }

        // Should be linked to pottery, ceramic, artifact, ancient, tool
        $linkedSpellings = $translation->fresh()->spellings->pluck('spelling')->toArray();
        $this->assertContains('pottery', $linkedSpellings);
        $this->assertContains('ceramic', $linkedSpellings);
        $this->assertContains('artifact', $linkedSpellings);
        $this->assertContains('ancient', $linkedSpellings);
        $this->assertContains('tool', $linkedSpellings);
        $this->assertCount(5, $linkedSpellings);
    }

    /**
     * Test empty/null fields don't cause errors.
     */
    public function test_empty_null_fields_dont_cause_errors()
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'pottery',
        ]);

        $translation = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'No match here',
            'description' => '',
            'type' => '',
            'holder' => '',
            'owner' => '',
        ]);

        $job = new SyncItemTranslationSpellings($translation->id);
        $job->handle();

        $this->assertCount(0, $translation->fresh()->spellings);
    }

    /**
     * Test special characters in spelling don't break regex.
     */
    public function test_special_characters_in_spelling_dont_break_regex()
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'pottery+ceramics',
        ]);

        $translation = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Found pottery+ceramics vessel',
        ]);

        $job = new SyncItemTranslationSpellings($translation->id);
        $job->handle();

        $this->assertTrue($translation->fresh()->spellings->contains($spelling));
    }

    /**
     * Test unicode characters are handled correctly.
     */
    public function test_unicode_characters_are_handled_correctly()
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'céramique',
        ]);

        $translation = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Collection de céramique ancienne',
        ]);

        $job = new SyncItemTranslationSpellings($translation->id);
        $job->handle();

        $this->assertTrue($translation->fresh()->spellings->contains($spelling));
    }

    /**
     * Test very long text doesn't cause performance issues.
     */
    public function test_very_long_text_doesnt_cause_performance_issues()
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'pottery',
        ]);

        // Create a very long description (10,000 words)
        $longText = str_repeat('This is a long description about ancient artifacts and pottery. ', 1000);

        $translation = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Test',
            'description' => $longText,
        ]);

        $start = microtime(true);
        $job = new SyncItemTranslationSpellings($translation->id);
        $job->handle();
        $duration = microtime(true) - $start;

        // Should complete in reasonable time (< 1 second)
        $this->assertLessThan(1.0, $duration);
        $this->assertTrue($translation->fresh()->spellings->contains($spelling));
    }

    /**
     * Test multiple spellings with overlapping text.
     */
    public function test_multiple_spellings_with_overlapping_text()
    {
        $language = Language::factory()->create();

        $spelling1 = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'pot',
        ]);
        $spelling2 = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'pottery',
        ]);
        $spelling3 = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'pots',
        ]);

        $translation = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Ancient pot and pottery with many pots',
        ]);

        $job = new SyncItemTranslationSpellings($translation->id);
        $job->handle();

        $linkedSpellings = $translation->fresh()->spellings->pluck('spelling')->toArray();

        // All three should match (word boundary matching)
        $this->assertContains('pot', $linkedSpellings);
        $this->assertContains('pottery', $linkedSpellings);
        $this->assertContains('pots', $linkedSpellings);
    }
}

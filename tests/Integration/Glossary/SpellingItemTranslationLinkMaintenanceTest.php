<?php

namespace Tests\Integration\Glossary;

use App\Jobs\SyncSpellingToItemTranslations;
use App\Models\GlossarySpelling;
use App\Models\ItemTranslation;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Integration\Traits\TestsGlossaryRelationships;
use Tests\TestCase;

class SpellingItemTranslationLinkMaintenanceTest extends TestCase
{
    use RefreshDatabase, TestsGlossaryRelationships;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    /**
     * Test creating spelling dispatches sync job.
     */
    public function test_creating_spelling_dispatches_sync_job()
    {
        $language = $this->createLanguageForGlossary();

        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'pottery',
        ]);

        Queue::assertPushed(SyncSpellingToItemTranslations::class, function ($job) use ($spelling) {
            return $job->spellingId === $spelling->id;
        });
    }

    /**
     * Test updating spelling dispatches sync job.
     */
    public function test_updating_spelling_dispatches_sync_job()
    {
        $language = $this->createLanguageForGlossary();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'pottery',
        ]);

        $spelling->update(['spelling' => 'ceramics']);

        // Should have pushed jobs for this spelling
        Queue::assertPushed(SyncSpellingToItemTranslations::class, function ($job) use ($spelling) {
            return $job->spellingId === $spelling->id;
        });
    }

    /**
     * Test deleting spelling detaches from all item translations synchronously.
     */
    public function test_deleting_spelling_detaches_from_all_item_translations_synchronously()
    {
        $language = $this->createLanguageForGlossary();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'artifact',
        ]);

        $translation1 = $this->createLinkedItemTranslation($spelling);
        $translation2 = $this->createLinkedItemTranslation($spelling);

        $this->assertCount(2, $spelling->itemTranslations);

        // Delete the spelling
        $spelling->delete();

        // All links should be gone
        $this->assertAllItemTranslationLinksRemoved($spelling->id);
    }

    /**
     * Test updating same spelling multiple times queues jobs.
     */
    public function test_updating_same_spelling_multiple_times_queues_jobs()
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'original',
        ]);

        // Update multiple times quickly
        $spelling->update(['spelling' => 'update1']);
        $spelling->update(['spelling' => 'update2']);
        $spelling->update(['spelling' => 'update3']);

        // Should have pushed jobs for this spelling
        Queue::assertPushed(SyncSpellingToItemTranslations::class, function ($job) use ($spelling) {
            return $job->spellingId === $spelling->id;
        });
    }

    /**
     * Test multiple distinct spellings updated consecutively.
     */
    public function test_multiple_distinct_spellings_updated_consecutively()
    {
        $language = Language::factory()->create();

        $spelling1 = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'pottery',
        ]);
        $spelling2 = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'ceramic',
        ]);
        $spelling3 = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'artifact',
        ]);

        // Update all three
        $spelling1->update(['spelling' => 'pottery-updated']);
        $spelling2->update(['spelling' => 'ceramic-updated']);
        $spelling3->update(['spelling' => 'artifact-updated']);

        // Verify each job has correct ID
        Queue::assertPushed(SyncSpellingToItemTranslations::class, function ($job) use ($spelling1) {
            return $job->spellingId === $spelling1->id;
        });
        Queue::assertPushed(SyncSpellingToItemTranslations::class, function ($job) use ($spelling2) {
            return $job->spellingId === $spelling2->id;
        });
        Queue::assertPushed(SyncSpellingToItemTranslations::class, function ($job) use ($spelling3) {
            return $job->spellingId === $spelling3->id;
        });
    }

    /**
     * Test delete spelling immediately after update is atomic.
     */
    public function test_delete_spelling_immediately_after_update_is_atomic()
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'pottery',
        ]);

        $translation = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Ancient Pottery',
        ]);

        // Attach spelling
        $translation->spellings()->attach($spelling->id);

        // Update and delete immediately
        $spelling->update(['spelling' => 'ceramics']);
        $spelling->delete();

        // The spelling link should be removed (synchronous deletion)
        $this->assertDatabaseMissing('item_translation_spelling', [
            'spelling_id' => $spelling->id,
        ]);

        // Jobs were dispatched for this spelling
        Queue::assertPushed(SyncSpellingToItemTranslations::class, function ($job) use ($spelling) {
            return $job->spellingId === $spelling->id;
        });
    }

    /**
     * Integration test: Create spelling links to matching item translations.
     */
    public function test_integration_create_spelling_links_to_matching_translations()
    {
        Queue::fake([]);

        $language = $this->createLanguageForGlossary();

        $translation1 = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Ancient Pottery',
        ]);
        $translation2 = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Pottery Collection',
        ]);
        $translation3 = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Metal Sword',
        ]);

        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'pottery',
        ]);

        // Manually dispatch the job
        $this->syncSpellingToItemTranslations($spelling);

        // Verify links were created
        $this->assertTranslationHasSpellings($translation1, [$spelling]);
        $this->assertTranslationHasSpellings($translation2, [$spelling]);
        $this->assertTranslationDoesNotHaveSpellings($translation3, [$spelling]);
    }

    /**
     * Integration test: Update spelling text re-syncs all translations.
     */
    public function test_integration_update_spelling_text_re_syncs_translations()
    {
        Queue::fake([]);

        $language = $this->createLanguageForGlossary();

        $translation1 = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Ancient Pottery',
        ]);
        $translation2 = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Ceramic Bowl',
        ]);

        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'pottery',
        ]);

        // Initial sync
        $this->syncSpellingToItemTranslations($spelling);

        $this->assertTranslationHasSpellings($translation1, [$spelling]);
        $this->assertTranslationDoesNotHaveSpellings($translation2, [$spelling]);

        // Update spelling to 'ceramic'
        $spelling->update(['spelling' => 'ceramic']);

        // Sync again
        $this->syncSpellingToItemTranslations($spelling);

        // Links should be re-synced
        $this->assertTranslationDoesNotHaveSpellings($translation1, [$spelling]);
        $this->assertTranslationHasSpellings($translation2, [$spelling]);
    }

    /**
     * Integration test: Delete spelling removes all links immediately.
     */
    public function test_integration_delete_spelling_removes_all_links_immediately()
    {
        Queue::fake([]);

        $language = $this->createLanguageForGlossary();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'artifact',
        ]);

        $translations = ItemTranslation::factory()->count(5)->create([
            'language_id' => $language->id,
            'name' => 'Ancient Artifact',
        ]);

        // Manually link spelling to all translations
        foreach ($translations as $translation) {
            $translation->spellings()->attach($spelling->id);
        }

        $this->assertCount(5, $spelling->fresh()->itemTranslations);

        // Delete the spelling
        $spellingId = $spelling->id;
        $spelling->delete();

        // All links should be gone
        $this->assertSpellingLinksRemoved($spellingId, $translations);
    }
}

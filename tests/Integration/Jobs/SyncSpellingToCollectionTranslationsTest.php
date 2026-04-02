<?php

namespace Tests\Integration\Jobs;

use App\Jobs\SyncSpellingToCollectionTranslations;
use App\Models\CollectionTranslation;
use App\Models\GlossarySpelling;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncSpellingToCollectionTranslationsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test job syncs spelling to matching collection translations.
     */
    public function test_syncs_spelling_to_matching_collection_translations()
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'pottery',
        ]);

        $translation1 = CollectionTranslation::factory()->create([
            'language_id' => $language->id,
            'title' => 'Ancient Pottery',
        ]);
        $translation2 = CollectionTranslation::factory()->create([
            'language_id' => $language->id,
            'title' => 'Pottery Collection',
        ]);
        $translation3 = CollectionTranslation::factory()->create([
            'language_id' => $language->id,
            'title' => 'Metal Swords',
        ]);

        $job = new SyncSpellingToCollectionTranslations($spelling->id);
        $job->handle();

        $this->assertTrue($translation1->fresh()->spellings->contains($spelling));
        $this->assertTrue($translation2->fresh()->spellings->contains($spelling));
        $this->assertFalse($translation3->fresh()->spellings->contains($spelling));
    }

    /**
     * Test job removes spelling from non-matching translations.
     */
    public function test_removes_spelling_from_non_matching_translations()
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'pottery',
        ]);

        $translation = CollectionTranslation::factory()->create([
            'language_id' => $language->id,
            'title' => 'Metal Swords',
        ]);

        // Manually attach the spelling
        $translation->spellings()->attach($spelling->id);
        $this->assertCount(1, $translation->fresh()->spellings);

        // Run the sync job
        $job = new SyncSpellingToCollectionTranslations($spelling->id);
        $job->handle();

        // The spelling should be removed
        $this->assertCount(0, $translation->fresh()->spellings);
    }

    /**
     * Test job handles deleted spelling gracefully.
     */
    public function test_handles_deleted_spelling_gracefully()
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
        ]);

        $spellingId = $spelling->id;
        $spelling->delete();

        $job = new SyncSpellingToCollectionTranslations($spellingId);
        $job->handle(); // Should not throw exception

        $this->assertTrue(true);
    }

    /**
     * Test job only processes translations in same language.
     */
    public function test_only_processes_translations_in_same_language()
    {
        $language1 = Language::factory()->create();
        $language2 = Language::factory()->create();

        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language1->id,
            'spelling' => 'artifact',
        ]);

        $translation1 = CollectionTranslation::factory()->create([
            'language_id' => $language1->id,
            'title' => 'Artifact Collection',
        ]);
        $translation2 = CollectionTranslation::factory()->create([
            'language_id' => $language2->id,
            'title' => 'Artifact Collection',
        ]);

        $job = new SyncSpellingToCollectionTranslations($spelling->id);
        $job->handle();

        $this->assertTrue($translation1->fresh()->spellings->contains($spelling));
        $this->assertFalse($translation2->fresh()->spellings->contains($spelling));
    }

    /**
     * Test job searches description and quote fields.
     */
    public function test_searches_description_and_quote_fields()
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'ceramic',
        ]);

        $translation1 = CollectionTranslation::factory()->create([
            'language_id' => $language->id,
            'title' => 'No match here',
            'description' => 'A ceramic collection from the 15th century',
        ]);
        $translation2 = CollectionTranslation::factory()->create([
            'language_id' => $language->id,
            'title' => 'No match here',
            'quote' => 'The ceramic arts flourished',
        ]);

        $job = new SyncSpellingToCollectionTranslations($spelling->id);
        $job->handle();

        $this->assertTrue($translation1->fresh()->spellings->contains($spelling));
        $this->assertTrue($translation2->fresh()->spellings->contains($spelling));
    }

    /**
     * Test job is idempotent (can be run multiple times).
     */
    public function test_is_idempotent()
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'pottery',
        ]);

        $translation = CollectionTranslation::factory()->create([
            'language_id' => $language->id,
            'title' => 'Ancient Pottery',
        ]);

        $job = new SyncSpellingToCollectionTranslations($spelling->id);

        // Run the job multiple times
        $job->handle();
        $job->handle();
        $job->handle();

        // Should still have exactly one link
        $this->assertCount(1, $translation->fresh()->spellings);
    }
}

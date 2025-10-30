<?php

namespace Tests\Integration\Jobs;

use App\Jobs\SyncSpellingToItemTranslations;
use App\Models\GlossarySpelling;
use App\Models\ItemTranslation;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncSpellingToItemTranslationsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test job syncs spelling to matching item translations.
     */
    public function test_syncs_spelling_to_matching_item_translations()
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'pottery',
        ]);

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

        $job = new SyncSpellingToItemTranslations($spelling->id);
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

        $translation = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Metal Sword',
        ]);

        // Manually attach the spelling
        $translation->spellings()->attach($spelling->id);
        $this->assertCount(1, $translation->fresh()->spellings);

        // Run the sync job
        $job = new SyncSpellingToItemTranslations($spelling->id);
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

        $job = new SyncSpellingToItemTranslations($spellingId);
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

        $translation1 = ItemTranslation::factory()->create([
            'language_id' => $language1->id,
            'name' => 'Artifact Collection',
        ]);
        $translation2 = ItemTranslation::factory()->create([
            'language_id' => $language2->id,
            'name' => 'Artifact Collection',
        ]);

        $job = new SyncSpellingToItemTranslations($spelling->id);
        $job->handle();

        $this->assertTrue($translation1->fresh()->spellings->contains($spelling));
        $this->assertFalse($translation2->fresh()->spellings->contains($spelling));
    }

    /**
     * Test job processes large datasets in chunks.
     */
    public function test_processes_large_datasets_in_chunks()
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'item',
        ]);

        // Create 150 translations (more than chunk size of 100)
        ItemTranslation::factory()->count(150)->create([
            'language_id' => $language->id,
            'name' => 'Test Item',
        ]);

        $job = new SyncSpellingToItemTranslations($spelling->id);
        $job->handle();

        // All 150 should be linked
        $this->assertEquals(150, $spelling->fresh()->itemTranslations()->count());
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

        $translation = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Ancient Pottery',
        ]);

        $job = new SyncSpellingToItemTranslations($spelling->id);

        // Run the job multiple times
        $job->handle();
        $job->handle();
        $job->handle();

        // Should still have exactly one link
        $this->assertCount(1, $translation->fresh()->spellings);
    }
}

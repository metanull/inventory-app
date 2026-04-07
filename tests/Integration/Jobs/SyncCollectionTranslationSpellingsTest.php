<?php

namespace Tests\Integration\Jobs;

use App\Jobs\SyncCollectionTranslationSpellings;
use App\Models\CollectionTranslation;
use App\Models\GlossarySpelling;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncCollectionTranslationSpellingsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test job syncs matching spellings to collection translation.
     */
    public function test_syncs_matching_spellings_to_collection_translation()
    {
        $language = Language::factory()->create();
        $spelling1 = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'artifact',
        ]);
        $spelling2 = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'ancient',
        ]);
        $spelling3 = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'ceramic',
        ]);

        $collectionTranslation = CollectionTranslation::factory()->create([
            'language_id' => $language->id,
            'title' => 'Ancient Artifact',
            'description' => 'An ancient artifact from Egypt',
        ]);

        $job = new SyncCollectionTranslationSpellings($collectionTranslation->id);
        $job->handle();

        $linkedSpellings = $collectionTranslation->fresh()->spellings->pluck('spelling')->toArray();
        $this->assertContains('artifact', $linkedSpellings);
        $this->assertContains('ancient', $linkedSpellings);
        $this->assertNotContains('ceramic', $linkedSpellings);
    }

    /**
     * Test job removes non-matching spellings.
     */
    public function test_removes_non_matching_spellings()
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'pottery',
        ]);

        $collectionTranslation = CollectionTranslation::factory()->create([
            'language_id' => $language->id,
            'title' => 'Metal Swords',
            'description' => 'A collection of metal swords',
        ]);

        // Manually attach the spelling
        $collectionTranslation->spellings()->attach($spelling->id);
        $this->assertCount(1, $collectionTranslation->fresh()->spellings);

        // Run the sync job
        $job = new SyncCollectionTranslationSpellings($collectionTranslation->id);
        $job->handle();

        // The spelling should be removed
        $this->assertCount(0, $collectionTranslation->fresh()->spellings);
    }

    /**
     * Test job handles deleted collection translation gracefully.
     */
    public function test_handles_deleted_collection_translation_gracefully()
    {
        $language = Language::factory()->create();
        $collectionTranslation = CollectionTranslation::factory()->create([
            'language_id' => $language->id,
        ]);

        $collectionTranslationId = $collectionTranslation->id;
        $collectionTranslation->delete();

        $job = new SyncCollectionTranslationSpellings($collectionTranslationId);
        $job->handle(); // Should not throw exception

        $this->assertTrue(true);
    }

    /**
     * Test job only links spellings in same language.
     */
    public function test_only_links_spellings_in_same_language()
    {
        $language1 = Language::factory()->create();
        $language2 = Language::factory()->create();

        $spelling1 = GlossarySpelling::factory()->create([
            'language_id' => $language1->id,
            'spelling' => 'artifact',
        ]);
        $spelling2 = GlossarySpelling::factory()->create([
            'language_id' => $language2->id,
            'spelling' => 'artifact',
        ]);

        $collectionTranslation = CollectionTranslation::factory()->create([
            'language_id' => $language1->id,
            'title' => 'Artifact',
        ]);

        $job = new SyncCollectionTranslationSpellings($collectionTranslation->id);
        $job->handle();

        $linkedSpellings = $collectionTranslation->fresh()->spellings;
        $this->assertCount(1, $linkedSpellings);
        $this->assertEquals($spelling1->id, $linkedSpellings->first()->id);
    }

    /**
     * Test job is case-insensitive.
     */
    public function test_matching_is_case_insensitive()
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'artifact',
        ]);

        $collectionTranslation = CollectionTranslation::factory()->create([
            'language_id' => $language->id,
            'title' => 'ARTIFACT Collection',
        ]);

        $job = new SyncCollectionTranslationSpellings($collectionTranslation->id);
        $job->handle();

        $linkedSpellings = $collectionTranslation->fresh()->spellings->pluck('spelling')->toArray();
        $this->assertContains('artifact', $linkedSpellings);
        $this->assertCount(1, $linkedSpellings);
    }
}

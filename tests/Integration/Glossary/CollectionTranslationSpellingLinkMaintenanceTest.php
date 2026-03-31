<?php

namespace Tests\Integration\Glossary;

use App\Jobs\SyncCollectionTranslationSpellings;
use App\Jobs\SyncSpellingToCollectionTranslations;
use App\Models\CollectionTranslation;
use App\Models\GlossarySpelling;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CollectionTranslationSpellingLinkMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    /**
     * Test creating collection translation dispatches sync job.
     */
    public function test_creating_collection_translation_dispatches_sync_job()
    {
        $language = Language::factory()->create();

        $collectionTranslation = CollectionTranslation::factory()->create([
            'language_id' => $language->id,
            'title' => 'Test Collection',
        ]);

        Queue::assertPushed(SyncCollectionTranslationSpellings::class, function ($job) use ($collectionTranslation) {
            return $job->collectionTranslationId === $collectionTranslation->id;
        });
    }

    /**
     * Test updating collection translation dispatches sync job.
     */
    public function test_updating_collection_translation_dispatches_sync_job()
    {
        $language = Language::factory()->create();
        $collectionTranslation = CollectionTranslation::factory()->create([
            'language_id' => $language->id,
            'title' => 'Original Title',
        ]);

        $collectionTranslation->update(['title' => 'Updated Title']);

        Queue::assertPushed(SyncCollectionTranslationSpellings::class, function ($job) use ($collectionTranslation) {
            return $job->collectionTranslationId === $collectionTranslation->id;
        });
    }

    /**
     * Test deleting collection translation detaches spellings synchronously.
     */
    public function test_deleting_collection_translation_detaches_spellings_synchronously()
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'artifact',
        ]);

        $collectionTranslation = CollectionTranslation::factory()->create([
            'language_id' => $language->id,
            'title' => 'Artifact Collection',
        ]);
        $collectionTranslation->spellings()->attach($spelling->id);

        $translationId = $collectionTranslation->id;
        $collectionTranslation->delete();

        $this->assertDatabaseMissing('collection_translation_spelling', [
            'collection_translation_id' => $translationId,
        ]);
    }

    /**
     * Test creating spelling dispatches sync job for collection translations.
     */
    public function test_creating_spelling_dispatches_sync_job_for_collection_translations()
    {
        $language = Language::factory()->create();

        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'pottery',
        ]);

        Queue::assertPushed(SyncSpellingToCollectionTranslations::class, function ($job) use ($spelling) {
            return $job->spellingId === $spelling->id;
        });
    }

    /**
     * Test deleting spelling detaches from all collection translations synchronously.
     */
    public function test_deleting_spelling_detaches_from_all_collection_translations_synchronously()
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'artifact',
        ]);

        $translation1 = CollectionTranslation::factory()->create([
            'language_id' => $language->id,
            'title' => 'Artifact 1',
        ]);
        $translation2 = CollectionTranslation::factory()->create([
            'language_id' => $language->id,
            'title' => 'Artifact 2',
        ]);
        $translation1->spellings()->attach($spelling->id);
        $translation2->spellings()->attach($spelling->id);

        $spelling->delete();

        $this->assertDatabaseMissing('collection_translation_spelling', [
            'spelling_id' => $spelling->id,
        ]);
    }

    /**
     * Integration test: Create collection translation with matching spelling.
     */
    public function test_integration_create_collection_translation_with_matching_spelling()
    {
        Queue::fake([]);

        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'ceramic',
        ]);

        $collectionTranslation = CollectionTranslation::factory()->create([
            'language_id' => $language->id,
            'title' => 'Ceramic Arts Collection',
        ]);

        // Manually dispatch the job (simulating queue worker)
        $job = new SyncCollectionTranslationSpellings($collectionTranslation->id);
        $job->handle();

        $this->assertTrue($collectionTranslation->fresh()->spellings->contains($spelling));
    }
}

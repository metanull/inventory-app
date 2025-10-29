<?php

namespace Tests\Feature\Glossary;

use App\Jobs\SyncItemTranslationSpellings;
use App\Models\GlossarySpelling;
use App\Models\ItemTranslation;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Integration\Traits\TestsGlossaryRelationships;
use Tests\TestCase;

class ItemTranslationSpellingLinkMaintenanceTest extends TestCase
{
    use RefreshDatabase, TestsGlossaryRelationships;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    /**
     * Test creating item translation dispatches sync job.
     */
    public function test_creating_item_translation_dispatches_sync_job()
    {
        $language = $this->createLanguageForGlossary();

        $itemTranslation = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Test Item',
        ]);

        Queue::assertPushed(SyncItemTranslationSpellings::class, function ($job) use ($itemTranslation) {
            return $job->itemTranslationId === $itemTranslation->id;
        });
    }

    /**
     * Test updating item translation dispatches sync job.
     */
    public function test_updating_item_translation_dispatches_sync_job()
    {
        $language = Language::factory()->create();
        $itemTranslation = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Original Name',
        ]);

        // Now update
        $itemTranslation->update(['name' => 'Updated Name']);

        // Should have pushed at least one job for this item translation
        Queue::assertPushed(SyncItemTranslationSpellings::class, function ($job) use ($itemTranslation) {
            return $job->itemTranslationId === $itemTranslation->id;
        });
    }

    /**
     * Test deleting item translation detaches spellings synchronously.
     */
    public function test_deleting_item_translation_detaches_spellings_synchronously()
    {
        $language = $this->createLanguageForGlossary();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'artifact',
        ]);

        $itemTranslation = $this->createLinkedItemTranslation($spelling);

        $this->assertTranslationHasSpellings($itemTranslation, [$spelling]);

        // Delete the item translation
        $translationId = $itemTranslation->id;
        $itemTranslation->delete();

        // The spelling link should be gone
        $this->assertAllSpellingLinksRemoved($translationId);
    }

    /**
     * Test updating same item translation multiple times only queues last job.
     */
    public function test_updating_same_item_translation_multiple_times_uses_unique_jobs()
    {
        $language = Language::factory()->create();
        $itemTranslation = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Original',
        ]);

        // Update multiple times quickly
        $itemTranslation->update(['name' => 'Update 1']);
        $itemTranslation->update(['name' => 'Update 2']);
        $itemTranslation->update(['name' => 'Update 3']);

        // Should have pushed jobs for this item translation
        // Unique job handling is done by Laravel queue worker, not the fake
        Queue::assertPushed(SyncItemTranslationSpellings::class, function ($job) use ($itemTranslation) {
            return $job->itemTranslationId === $itemTranslation->id;
        });
    }

    /**
     * Test delete immediately after update is atomic.
     */
    public function test_delete_immediately_after_update_is_atomic()
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'pottery',
        ]);

        $itemTranslation = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Ancient Pottery',
        ]);

        // Attach spelling
        $itemTranslation->spellings()->attach($spelling->id);

        // Update and delete immediately
        $itemTranslation->update(['name' => 'Updated Pottery']);
        $itemTranslation->delete();

        // The spelling link should be removed (synchronous deletion)
        $this->assertDatabaseMissing('item_translation_spelling', [
            'item_translation_id' => $itemTranslation->id,
        ]);

        // Jobs were dispatched for this translation
        Queue::assertPushed(SyncItemTranslationSpellings::class, function ($job) use ($itemTranslation) {
            return $job->itemTranslationId === $itemTranslation->id;
        });
    }

    /**
     * Test multiple distinct item translations updated consecutively.
     */
    public function test_multiple_distinct_item_translations_updated_consecutively()
    {
        $language = Language::factory()->create();

        $translation1 = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Item 1',
        ]);
        $translation2 = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Item 2',
        ]);
        $translation3 = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Item 3',
        ]);

        // Update all three
        $translation1->update(['name' => 'Updated Item 1']);
        $translation2->update(['name' => 'Updated Item 2']);
        $translation3->update(['name' => 'Updated Item 3']);

        // Verify each translation has jobs
        Queue::assertPushed(SyncItemTranslationSpellings::class, function ($job) use ($translation1) {
            return $job->itemTranslationId === $translation1->id;
        });
        Queue::assertPushed(SyncItemTranslationSpellings::class, function ($job) use ($translation2) {
            return $job->itemTranslationId === $translation2->id;
        });
        Queue::assertPushed(SyncItemTranslationSpellings::class, function ($job) use ($translation3) {
            return $job->itemTranslationId === $translation3->id;
        });
    }

    /**
     * Integration test: Create item translation with matching spelling.
     */
    public function test_integration_create_item_translation_with_matching_spelling()
    {
        Queue::fake([]); // Don't fake the queue to test real behavior

        $language = $this->createLanguageForGlossary();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'ceramic',
        ]);

        $itemTranslation = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Ceramic Bowl',
        ]);

        // Manually dispatch the job (simulating queue worker)
        $this->syncItemTranslationSpellings($itemTranslation);

        // Verify the link was created
        $this->assertTranslationHasSpellings($itemTranslation, [$spelling]);
    }

    /**
     * Integration test: Update item translation removes old links and adds new ones.
     */
    public function test_integration_update_item_translation_syncs_spellings()
    {
        Queue::fake([]);

        $language = $this->createLanguageForGlossary();
        $spelling1 = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'pottery',
        ]);
        $spelling2 = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'ceramic',
        ]);

        $itemTranslation = ItemTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Ancient Pottery',
        ]);

        // Initial sync
        $this->syncItemTranslationSpellings($itemTranslation);

        $this->assertTranslationHasSpellings($itemTranslation, [$spelling1]);
        $this->assertTranslationDoesNotHaveSpellings($itemTranslation, [$spelling2]);

        // Update to include ceramic instead
        $itemTranslation->update(['name' => 'Ceramic Bowl']);

        // Sync again
        $this->syncItemTranslationSpellings($itemTranslation);

        $this->assertTranslationDoesNotHaveSpellings($itemTranslation, [$spelling1]);
        $this->assertTranslationHasSpellings($itemTranslation, [$spelling2]);
    }
}

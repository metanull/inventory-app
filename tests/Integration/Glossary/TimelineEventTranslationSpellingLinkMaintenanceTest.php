<?php

namespace Tests\Integration\Glossary;

use App\Jobs\SyncSpellingToTimelineEventTranslations;
use App\Jobs\SyncTimelineEventTranslationSpellings;
use App\Models\GlossarySpelling;
use App\Models\Language;
use App\Models\TimelineEventTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TimelineEventTranslationSpellingLinkMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
    }

    /**
     * Test creating timeline event translation dispatches sync job.
     */
    public function test_creating_timeline_event_translation_dispatches_sync_job()
    {
        $language = Language::factory()->create();

        $timelineEventTranslation = TimelineEventTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Test Event',
        ]);

        Queue::assertPushed(SyncTimelineEventTranslationSpellings::class, function ($job) use ($timelineEventTranslation) {
            return $job->timelineEventTranslationId === $timelineEventTranslation->id;
        });
    }

    /**
     * Test updating timeline event translation dispatches sync job.
     */
    public function test_updating_timeline_event_translation_dispatches_sync_job()
    {
        $language = Language::factory()->create();
        $timelineEventTranslation = TimelineEventTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Original Name',
        ]);

        $timelineEventTranslation->update(['name' => 'Updated Name']);

        Queue::assertPushed(SyncTimelineEventTranslationSpellings::class, function ($job) use ($timelineEventTranslation) {
            return $job->timelineEventTranslationId === $timelineEventTranslation->id;
        });
    }

    /**
     * Test deleting timeline event translation detaches spellings synchronously.
     */
    public function test_deleting_timeline_event_translation_detaches_spellings_synchronously()
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'dynasty',
        ]);

        $timelineEventTranslation = TimelineEventTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Umayyad Dynasty',
        ]);
        $timelineEventTranslation->spellings()->attach($spelling->id);

        $translationId = $timelineEventTranslation->id;
        $timelineEventTranslation->delete();

        $this->assertDatabaseMissing('timeline_event_translation_spelling', [
            'timeline_event_translation_id' => $translationId,
        ]);
    }

    /**
     * Test creating spelling dispatches sync job for timeline event translations.
     */
    public function test_creating_spelling_dispatches_sync_job_for_timeline_event_translations()
    {
        $language = Language::factory()->create();

        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'dynasty',
        ]);

        Queue::assertPushed(SyncSpellingToTimelineEventTranslations::class, function ($job) use ($spelling) {
            return $job->spellingId === $spelling->id;
        });
    }

    /**
     * Test deleting spelling detaches from all timeline event translations synchronously.
     */
    public function test_deleting_spelling_detaches_from_all_timeline_event_translations_synchronously()
    {
        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'dynasty',
        ]);

        $translation1 = TimelineEventTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Dynasty Event 1',
        ]);
        $translation2 = TimelineEventTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Dynasty Event 2',
        ]);
        $translation1->spellings()->attach($spelling->id);
        $translation2->spellings()->attach($spelling->id);

        $spelling->delete();

        $this->assertDatabaseMissing('timeline_event_translation_spelling', [
            'spelling_id' => $spelling->id,
        ]);
    }

    /**
     * Integration test: Create timeline event translation with matching spelling.
     */
    public function test_integration_create_timeline_event_translation_with_matching_spelling()
    {
        Queue::fake([]);

        $language = Language::factory()->create();
        $spelling = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'dynasty',
        ]);

        $timelineEventTranslation = TimelineEventTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Umayyad Dynasty',
        ]);

        // Manually dispatch the job (simulating queue worker)
        $job = new SyncTimelineEventTranslationSpellings($timelineEventTranslation->id);
        $job->handle();

        $this->assertTrue($timelineEventTranslation->fresh()->spellings->contains($spelling));
    }
}

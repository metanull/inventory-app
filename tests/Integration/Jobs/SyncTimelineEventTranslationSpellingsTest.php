<?php

namespace Tests\Integration\Jobs;

use App\Jobs\SyncTimelineEventTranslationSpellings;
use App\Models\GlossarySpelling;
use App\Models\Language;
use App\Models\TimelineEventTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SyncTimelineEventTranslationSpellingsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test job syncs matching spellings to timeline event translation.
     */
    public function test_syncs_matching_spellings_to_timeline_event_translation()
    {
        $language = Language::factory()->create();
        $spelling1 = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'dynasty',
        ]);
        $spelling2 = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'umayyad',
        ]);
        $spelling3 = GlossarySpelling::factory()->create([
            'language_id' => $language->id,
            'spelling' => 'ceramic',
        ]);

        $timelineEventTranslation = TimelineEventTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Umayyad Dynasty',
            'description' => 'The Umayyad dynasty was a major caliphate',
        ]);

        $job = new SyncTimelineEventTranslationSpellings($timelineEventTranslation->id);
        $job->handle();

        $linkedSpellings = $timelineEventTranslation->fresh()->spellings->pluck('spelling')->toArray();
        $this->assertContains('dynasty', $linkedSpellings);
        $this->assertContains('umayyad', $linkedSpellings);
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

        $timelineEventTranslation = TimelineEventTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'Battle of Tours',
            'description' => 'A major battle in 732',
        ]);

        // Manually attach the spelling
        $timelineEventTranslation->spellings()->attach($spelling->id);
        $this->assertCount(1, $timelineEventTranslation->fresh()->spellings);

        // Run the sync job
        $job = new SyncTimelineEventTranslationSpellings($timelineEventTranslation->id);
        $job->handle();

        // The spelling should be removed
        $this->assertCount(0, $timelineEventTranslation->fresh()->spellings);
    }

    /**
     * Test job handles deleted timeline event translation gracefully.
     */
    public function test_handles_deleted_timeline_event_translation_gracefully()
    {
        $language = Language::factory()->create();
        $timelineEventTranslation = TimelineEventTranslation::factory()->create([
            'language_id' => $language->id,
        ]);

        $timelineEventTranslationId = $timelineEventTranslation->id;
        $timelineEventTranslation->delete();

        $job = new SyncTimelineEventTranslationSpellings($timelineEventTranslationId);
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
            'spelling' => 'dynasty',
        ]);
        $spelling2 = GlossarySpelling::factory()->create([
            'language_id' => $language2->id,
            'spelling' => 'dynasty',
        ]);

        $timelineEventTranslation = TimelineEventTranslation::factory()->create([
            'language_id' => $language1->id,
            'name' => 'Umayyad Dynasty',
        ]);

        $job = new SyncTimelineEventTranslationSpellings($timelineEventTranslation->id);
        $job->handle();

        $linkedSpellings = $timelineEventTranslation->fresh()->spellings;
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
            'spelling' => 'dynasty',
        ]);

        $timelineEventTranslation = TimelineEventTranslation::factory()->create([
            'language_id' => $language->id,
            'name' => 'UMAYYAD DYNASTY',
        ]);

        $job = new SyncTimelineEventTranslationSpellings($timelineEventTranslation->id);
        $job->handle();

        $linkedSpellings = $timelineEventTranslation->fresh()->spellings->pluck('spelling')->toArray();
        $this->assertContains('dynasty', $linkedSpellings);
        $this->assertCount(1, $linkedSpellings);
    }
}

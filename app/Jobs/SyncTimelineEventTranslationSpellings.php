<?php

namespace App\Jobs;

use App\Models\GlossarySpelling;
use App\Models\TimelineEventTranslation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SyncTimelineEventTranslationSpellings implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly string $timelineEventTranslationId
    ) {
        $this->onQueue('glossary');
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return 'sync-timeline-event-translation-'.$this->timelineEventTranslationId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $timelineEventTranslation = TimelineEventTranslation::find($this->timelineEventTranslationId);

        // If TimelineEventTranslation was deleted, nothing to do
        if (! $timelineEventTranslation) {
            return;
        }

        // Get all spellings for this language
        $spellings = GlossarySpelling::where('language_id', $timelineEventTranslation->language_id)->get();

        // Find matching spellings in the translation text
        $matchingSpellingIds = [];
        $searchText = $this->buildSearchText($timelineEventTranslation);

        foreach ($spellings as $spelling) {
            if ($this->textContainsSpelling($searchText, $spelling->spelling)) {
                $matchingSpellingIds[] = $spelling->id;
            }
        }

        // Sync the relationships (atomic operation)
        DB::transaction(function () use ($timelineEventTranslation, $matchingSpellingIds) {
            $timelineEventTranslation->spellings()->sync($matchingSpellingIds);
        });
    }

    /**
     * Build search text from all relevant TimelineEventTranslation fields.
     */
    private function buildSearchText(TimelineEventTranslation $timelineEventTranslation): string
    {
        $fields = [
            $timelineEventTranslation->name,
            $timelineEventTranslation->description,
            $timelineEventTranslation->date_from_description,
            $timelineEventTranslation->date_to_description,
            $timelineEventTranslation->date_from_ah_description,
        ];

        return implode(' ', array_filter($fields));
    }

    /**
     * Check if text contains the spelling using word boundary matching.
     */
    private function textContainsSpelling(string $text, string $spelling): bool
    {
        // Use word boundary matching with Unicode support and case-insensitive
        $pattern = '/\b'.preg_quote($spelling, '/').'\b/ui';

        return preg_match($pattern, $text) === 1;
    }
}

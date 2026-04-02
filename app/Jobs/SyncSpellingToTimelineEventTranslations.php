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

class SyncSpellingToTimelineEventTranslations implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly string $spellingId
    ) {
        $this->onQueue('glossary');
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return 'sync-spelling-timeline-event-'.$this->spellingId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $spelling = GlossarySpelling::find($this->spellingId);

        // If Spelling was deleted, nothing to do
        if (! $spelling) {
            return;
        }

        // Process in chunks to avoid memory issues
        TimelineEventTranslation::where('language_id', $spelling->language_id)
            ->chunk(100, function ($translations) use ($spelling) {
                foreach ($translations as $translation) {
                    $this->syncSpellingToTranslation($translation, $spelling);
                }
            });
    }

    /**
     * Sync spelling to a single translation.
     */
    private function syncSpellingToTranslation(TimelineEventTranslation $translation, GlossarySpelling $spelling): void
    {
        $searchText = $this->buildSearchText($translation);

        $matches = $this->textContainsSpelling($searchText, $spelling->spelling);

        DB::transaction(function () use ($translation, $spelling, $matches) {
            if ($matches) {
                // Add the spelling link if not already present
                $translation->spellings()->syncWithoutDetaching([$spelling->id]);
            } else {
                // Remove the spelling link if present
                $translation->spellings()->detach($spelling->id);
            }
        });
    }

    /**
     * Build search text from all relevant TimelineEventTranslation fields.
     */
    private function buildSearchText(TimelineEventTranslation $translation): string
    {
        $fields = [
            $translation->name,
            $translation->description,
            $translation->date_from_description,
            $translation->date_to_description,
            $translation->date_from_ah_description,
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

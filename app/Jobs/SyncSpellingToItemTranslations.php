<?php

namespace App\Jobs;

use App\Models\GlossarySpelling;
use App\Models\ItemTranslation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SyncSpellingToItemTranslations implements ShouldBeUnique, ShouldQueue
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
        return 'sync-spelling-'.$this->spellingId;
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
        ItemTranslation::where('language_id', $spelling->language_id)
            ->chunk(100, function ($translations) use ($spelling) {
                foreach ($translations as $translation) {
                    $this->syncSpellingToTranslation($translation, $spelling);
                }
            });
    }

    /**
     * Sync spelling to a single translation.
     */
    private function syncSpellingToTranslation(ItemTranslation $translation, GlossarySpelling $spelling): void
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
     * Build search text from all relevant ItemTranslation fields.
     */
    private function buildSearchText(ItemTranslation $translation): string
    {
        $fields = [
            $translation->name,
            $translation->alternate_name,
            $translation->description,
            $translation->type,
            $translation->holder,
            $translation->owner,
            $translation->initial_owner,
            $translation->dates,
            $translation->location,
            $translation->dimensions,
            $translation->place_of_production,
            $translation->method_for_datation,
            $translation->method_for_provenance,
            $translation->obtention,
            $translation->bibliography,
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

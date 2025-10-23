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

class SyncItemTranslationSpellings implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly string $itemTranslationId
    ) {
        $this->onQueue('glossary');
    }

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return 'sync-item-translation-'.$this->itemTranslationId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $itemTranslation = ItemTranslation::find($this->itemTranslationId);

        // If ItemTranslation was deleted, nothing to do
        if (! $itemTranslation) {
            return;
        }

        // Get all spellings for this language
        $spellings = GlossarySpelling::where('language_id', $itemTranslation->language_id)->get();

        // Find matching spellings in the translation text
        $matchingSpellingIds = [];
        $searchText = $this->buildSearchText($itemTranslation);

        foreach ($spellings as $spelling) {
            if ($this->textContainsSpelling($searchText, $spelling->spelling)) {
                $matchingSpellingIds[] = $spelling->id;
            }
        }

        // Sync the relationships (atomic operation)
        DB::transaction(function () use ($itemTranslation, $matchingSpellingIds) {
            $itemTranslation->spellings()->sync($matchingSpellingIds);
        });
    }

    /**
     * Build search text from all relevant ItemTranslation fields.
     */
    private function buildSearchText(ItemTranslation $itemTranslation): string
    {
        $fields = [
            $itemTranslation->name,
            $itemTranslation->alternate_name,
            $itemTranslation->description,
            $itemTranslation->type,
            $itemTranslation->holder,
            $itemTranslation->owner,
            $itemTranslation->initial_owner,
            $itemTranslation->dates,
            $itemTranslation->location,
            $itemTranslation->dimensions,
            $itemTranslation->place_of_production,
            $itemTranslation->method_for_datation,
            $itemTranslation->method_for_provenance,
            $itemTranslation->obtention,
            $itemTranslation->bibliography,
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

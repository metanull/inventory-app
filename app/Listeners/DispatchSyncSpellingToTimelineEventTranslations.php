<?php

namespace App\Listeners;

use App\Events\SpellingSaved;
use App\Jobs\SyncSpellingToTimelineEventTranslations;

class DispatchSyncSpellingToTimelineEventTranslations
{
    /**
     * Handle the event.
     */
    public function handle(SpellingSaved $event): void
    {
        SyncSpellingToTimelineEventTranslations::dispatch($event->spelling->id);
    }
}

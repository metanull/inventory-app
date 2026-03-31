<?php

namespace App\Listeners;

use App\Events\TimelineEventTranslationSaved;
use App\Jobs\SyncTimelineEventTranslationSpellings;

class DispatchSyncTimelineEventTranslationSpellings
{
    /**
     * Handle the event.
     */
    public function handle(TimelineEventTranslationSaved $event): void
    {
        SyncTimelineEventTranslationSpellings::dispatch($event->timelineEventTranslation->id);
    }
}

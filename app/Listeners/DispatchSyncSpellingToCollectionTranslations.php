<?php

namespace App\Listeners;

use App\Events\SpellingSaved;
use App\Jobs\SyncSpellingToCollectionTranslations;

class DispatchSyncSpellingToCollectionTranslations
{
    /**
     * Handle the event.
     */
    public function handle(SpellingSaved $event): void
    {
        SyncSpellingToCollectionTranslations::dispatch($event->spelling->id);
    }
}

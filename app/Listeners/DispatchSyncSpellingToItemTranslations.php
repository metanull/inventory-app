<?php

namespace App\Listeners;

use App\Events\SpellingSaved;
use App\Jobs\SyncSpellingToItemTranslations;

class DispatchSyncSpellingToItemTranslations
{
    /**
     * Handle the event.
     */
    public function handle(SpellingSaved $event): void
    {
        SyncSpellingToItemTranslations::dispatch($event->spelling->id);
    }
}

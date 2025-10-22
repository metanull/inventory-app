<?php

namespace App\Listeners;

use App\Events\ItemTranslationSaved;
use App\Jobs\SyncItemTranslationSpellings;

class DispatchSyncItemTranslationSpellings
{
    /**
     * Handle the event.
     */
    public function handle(ItemTranslationSaved $event): void
    {
        SyncItemTranslationSpellings::dispatch($event->itemTranslation->id);
    }
}

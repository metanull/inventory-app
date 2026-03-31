<?php

namespace App\Listeners;

use App\Events\CollectionTranslationSaved;
use App\Jobs\SyncCollectionTranslationSpellings;

class DispatchSyncCollectionTranslationSpellings
{
    /**
     * Handle the event.
     */
    public function handle(CollectionTranslationSaved $event): void
    {
        SyncCollectionTranslationSpellings::dispatch($event->collectionTranslation->id);
    }
}

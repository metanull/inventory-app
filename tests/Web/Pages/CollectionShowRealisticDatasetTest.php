<?php

namespace Tests\Web\Pages;

use App\Models\Collection;
use App\Models\CollectionImage;
use App\Models\CollectionTranslation;
use App\Models\Item;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\RendersShowPageUnderRealisticDataset;

/**
 * Realistic-dataset HTTP test for GET /web/collections/{id}.
 *
 * Seeds a production-like number of related rows to surface memory-exhaustion
 * and query-count regressions that a single-record test would not catch.
 */
class CollectionShowRealisticDatasetTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use RendersShowPageUnderRealisticDataset;

    /**
     * 30 covers collection eager-loads (context, language, parent, children,
     * translations, attachedItems.itemImages, collectionImages) plus auth
     * overhead.  The page does not embed full-table option snapshots.
     */
    protected function queryBudget(): int
    {
        return 30;
    }

    /**
     * 1 MB ceiling — with dynamic-mode Livewire selects the page should be
     * well within this; we keep a generous default for future additions.
     */
    protected function responseSizeBudget(): int
    {
        return 1_000_000;
    }

    protected function getShowRouteName(): string
    {
        return 'collections.show';
    }

    protected function seedRealisticDataset(): Model
    {
        $subject = Collection::factory()->create();

        // ≥ 200 sibling collections to verify no full-table load
        Collection::factory()->count(200)->create();

        // ≥ 20 child collections attached to the subject
        Collection::factory()->count(20)->create(['parent_id' => $subject->id, 'display_order' => null]);

        // ≥ 50 items attached to the subject
        $items = Item::factory()->count(50)->create();
        $subject->attachedItems()->attach($items->pluck('id'));

        // ≥ 10 translations attached to the subject
        CollectionTranslation::factory()->count(10)->create(['collection_id' => $subject->id]);

        // ≥ 5 images attached to the subject
        CollectionImage::factory()->count(5)->forCollection($subject)->create();

        return $subject;
    }
}

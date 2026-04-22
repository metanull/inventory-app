<?php

namespace Tests\Web\Pages;

use App\Models\Item;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\RendersShowPageUnderRealisticDataset;

/**
 * Realistic-dataset HTTP test for GET /web/tags/{id}.
 *
 * Seeds a production-like number of related rows to surface memory-exhaustion
 * and query-count regressions that a single-record test would not catch.
 */
class TagShowRealisticDatasetTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use RendersShowPageUnderRealisticDataset;

    /**
     * 15 covers auth overhead, single record fetch, and the item count
     * aggregation query.  No option snapshots are embedded.
     */
    protected function queryBudget(): int
    {
        return 15;
    }

    /**
     * 500 KB ceiling — the show page displays only a count link, not the items.
     */
    protected function responseSizeBudget(): int
    {
        return 500_000;
    }

    protected function getShowRouteName(): string
    {
        return 'tags.show';
    }

    protected function seedRealisticDataset(): Model
    {
        $subject = Tag::factory()->create();

        // ≥ 200 sibling tags to verify no full-table load
        Tag::factory()->count(200)->create();

        // ≥ 100 items tagged with the subject to exercise the count query
        $items = Item::factory()->count(100)->create();
        $subject->items()->attach($items->pluck('id'));

        return $subject;
    }
}

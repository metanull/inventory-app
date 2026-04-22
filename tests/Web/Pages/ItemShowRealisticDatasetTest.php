<?php

namespace Tests\Web\Pages;

use App\Models\Item;
use App\Models\ItemItemLink;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\RendersShowPageUnderRealisticDataset;

/**
 * Realistic-dataset HTTP test for GET /web/items/{id}.
 *
 * Seeds a production-like number of related rows to surface memory-exhaustion
 * and query-count regressions that a single-record test would not catch.
 */
class ItemShowRealisticDatasetTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use RendersShowPageUnderRealisticDataset;

    /**
     * 25 covers ~8 eager-loaded relations with framework overhead.
     * A full-table get() on items alone would push well past this.
     */
    protected function queryBudget(): int
    {
        return 25;
    }

    /**
     * 1 MB ceiling — Livewire snapshots embedding 500+ item rows easily
     * exceed this in the static-options regime.
     */
    protected function responseSizeBudget(): int
    {
        return 1_000_000;
    }

    protected function getShowRouteName(): string
    {
        return 'items.show';
    }

    protected function seedRealisticDataset(): Model
    {
        $subject = Item::factory()->create();

        // ≥ 500 sibling items (not related to the subject)
        Item::factory()->count(500)->create();

        // ≥ 50 children attached to the subject
        Item::factory()->count(50)->withParent($subject)->create();

        // ≥ 200 tags; attach ≥ 50 of them to the subject
        $allTags = Tag::factory()->count(200)->create();
        $subject->tags()->attach($allTags->take(50)->pluck('id'));

        // ≥ 20 outgoing ItemItemLink records from the subject
        ItemItemLink::factory()->count(20)->fromSource($subject)->create();

        // ≥ 20 incoming ItemItemLink records targeting the subject
        ItemItemLink::factory()->count(20)->toTarget($subject)->create();

        return $subject;
    }
}

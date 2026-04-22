<?php

namespace Tests\Web\Pages;

use App\Models\Item;
use App\Models\ItemItemLink;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;

/**
 * Realistic-dataset HTTP test for GET /web/items/{id}.
 *
 * Seeds a production-like number of related rows to surface memory-exhaustion
 * and query-count regressions that a single-record test would not catch.
 *
 * Budget constants (documenting the "why" for reviewers and future tuners):
 *
 *  QUERY_BUDGET        – Maximum number of DB queries the show page may issue.
 *                        25 is generous for a page that eager-loads ~8 relations.
 *                        A full-table get() for the items table alone would add
 *                        queries and (more critically) explode memory/response size.
 *
 *  RESPONSE_SIZE_BUDGET – Maximum allowed response body in bytes (1 MB).
 *                         With static-options Livewire snapshots for 500+ items
 *                         the dehydrated snapshot alone reaches several MB.
 */
class ItemShowRealisticDatasetTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;

    /**
     * Maximum number of DB queries the show page may issue.
     * Full-table get() calls would inflate this far above 25.
     */
    private const QUERY_BUDGET = 25;

    /**
     * Maximum allowed response body size in bytes.
     * Livewire snapshots containing 500+ item rows exceed this easily.
     */
    private const RESPONSE_SIZE_BUDGET = 1_000_000; // 1 MB

    public function test_show_page_renders_under_realistic_dataset(): void
    {
        // ── Seed a realistic dataset ──────────────────────────────────────

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

        // ── Issue the request and collect diagnostics ──────────────────────
        DB::enableQueryLog();

        $response = $this->get(route('items.show', $subject));

        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $responseSize = strlen($response->getContent());

        // ── Assertions ────────────────────────────────────────────────────
        $response->assertOk();

        $this->assertLessThanOrEqual(
            self::QUERY_BUDGET,
            $queryCount,
            "Show page issued {$queryCount} queries; budget is ".self::QUERY_BUDGET.'.',
        );

        $this->assertLessThanOrEqual(
            self::RESPONSE_SIZE_BUDGET,
            $responseSize,
            "Response body is {$responseSize} bytes; budget is ".self::RESPONSE_SIZE_BUDGET.'.',
        );
    }
}

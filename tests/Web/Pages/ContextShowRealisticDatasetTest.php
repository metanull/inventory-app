<?php

namespace Tests\Web\Pages;

use App\Models\Context;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\RendersShowPageUnderRealisticDataset;

/**
 * Realistic-dataset HTTP test for GET /web/contexts/{id}.
 *
 * Seeds a production-like number of sibling rows to verify no full-table
 * loads are issued from the show page.
 */
class ContextShowRealisticDatasetTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use RendersShowPageUnderRealisticDataset;

    /**
     * 15 covers auth overhead and the single record fetch.
     * The context show page has no relationship sections.
     */
    protected function queryBudget(): int
    {
        return 15;
    }

    /**
     * 500 KB ceiling — minimal page with no dynamic option snapshots.
     */
    protected function responseSizeBudget(): int
    {
        return 500_000;
    }

    protected function getShowRouteName(): string
    {
        return 'contexts.show';
    }

    protected function seedRealisticDataset(): Model
    {
        $subject = Context::factory()->create();

        // ≥ 200 sibling contexts to verify no full-table load
        Context::factory()->count(200)->create();

        return $subject;
    }
}

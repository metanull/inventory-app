<?php

namespace Tests\Web\Pages;

use App\Models\Country;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\RendersShowPageUnderRealisticDataset;

/**
 * Realistic-dataset HTTP test for GET /web/countries/{id}.
 *
 * Seeds a production-like number of sibling rows to verify no full-table
 * loads are issued from the show page.
 */
class CountryShowRealisticDatasetTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use RendersShowPageUnderRealisticDataset;

    /**
     * 15 covers auth overhead and the single record fetch.
     * The country show page has no relationship sections.
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
        return 'countries.show';
    }

    protected function seedRealisticDataset(): Model
    {
        $subject = Country::factory()->create();

        // ≥ 100 sibling countries to verify no full-table load
        Country::factory()->count(100)->create();

        return $subject;
    }
}

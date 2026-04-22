<?php

namespace Tests\Web\Pages;

use App\Models\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\RendersShowPageUnderRealisticDataset;

/**
 * Realistic-dataset HTTP test for GET /web/languages/{id}.
 *
 * Seeds a production-like number of sibling rows to verify no full-table
 * loads are issued from the show page.
 */
class LanguageShowRealisticDatasetTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use RendersShowPageUnderRealisticDataset;

    /**
     * 15 covers auth overhead and the single record fetch.
     * The language show page has no relationship sections.
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
        return 'languages.show';
    }

    protected function seedRealisticDataset(): Model
    {
        $subject = Language::factory()->create();

        // ≥ 100 sibling languages to verify no full-table load
        Language::factory()->count(100)->create();

        return $subject;
    }
}

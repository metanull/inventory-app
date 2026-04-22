<?php

namespace Tests\Web\Pages;

use App\Models\Context;
use App\Models\Language;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\RendersShowPageUnderRealisticDataset;

/**
 * Realistic-dataset HTTP test for GET /web/projects/{id}.
 *
 * Seeds a production-like number of sibling rows to verify no full-table
 * loads are issued from the show page.
 */
class ProjectShowRealisticDatasetTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use RendersShowPageUnderRealisticDataset;

    /**
     * 15 covers auth overhead, single record fetch, and context/language
     * eager-loads.  No option snapshots are embedded.
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
        return 'projects.show';
    }

    protected function seedRealisticDataset(): Model
    {
        $context = Context::factory()->create();
        $language = Language::factory()->create();

        $subject = Project::factory()->create([
            'context_id' => $context->id,
            'language_id' => $language->id,
        ]);

        // ≥ 200 sibling projects to verify no full-table load
        Project::factory()->count(200)->create();

        return $subject;
    }
}

<?php

namespace Tests\Web\Pages;

use App\Models\Glossary;
use App\Models\GlossarySpelling;
use App\Models\GlossaryTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\RendersShowPageUnderRealisticDataset;

/**
 * Realistic-dataset HTTP test for GET /web/glossaries/{id}.
 *
 * Seeds a production-like number of related rows to surface memory-exhaustion
 * and query-count regressions that a single-record test would not catch.
 */
class GlossaryShowRealisticDatasetTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use RendersShowPageUnderRealisticDataset;

    /**
     * 20 covers eager-loads (translations.language, spellings.language, synonyms)
     * plus auth overhead.  No option snapshots are embedded.
     */
    protected function queryBudget(): int
    {
        return 20;
    }

    /**
     * 500 KB ceiling — the glossary show page has no Livewire snapshot bloat.
     */
    protected function responseSizeBudget(): int
    {
        return 500_000;
    }

    protected function getShowRouteName(): string
    {
        return 'glossaries.show';
    }

    protected function seedRealisticDataset(): Model
    {
        $subject = Glossary::factory()->create();

        // ≥ 200 sibling glossaries to verify no full-table load
        Glossary::factory()->count(200)->create();

        // ≥ 10 translations attached to the subject
        GlossaryTranslation::factory()->count(10)->create(['glossary_id' => $subject->id]);

        // ≥ 10 spellings attached to the subject
        GlossarySpelling::factory()->count(10)->create(['glossary_id' => $subject->id]);

        return $subject;
    }
}

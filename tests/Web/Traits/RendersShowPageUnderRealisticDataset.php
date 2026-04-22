<?php

namespace Tests\Web\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Pest/PHPUnit trait that runs a single HTTP GET against an entity show page
 * seeded with a production-like dataset and asserts three quality budgets:
 *
 *   1. HTTP 200 – the page must not error out.
 *   2. Query budget – `DB::enableQueryLog()` counts queries; full-table
 *      `get()` calls would push this far above the default of 25.
 *   3. Response-size ceiling – a Livewire snapshot that embeds hundreds of
 *      option rows can easily reach several MB; default ceiling is 1 MB.
 *
 * ## How to use
 *
 * 1. Add the trait to your test class alongside `RefreshDatabase` and
 *    `AuthenticatesWebRequests`.
 * 2. Implement `seedRealisticDataset()` – create the subject model and seed
 *    enough related rows to expose N+1 / memory regressions.  Return the
 *    subject model.
 * 3. Implement `getShowRouteName()` – return the Laravel route name for the
 *    show action, e.g. `'items.show'`.
 * 4. Override `queryBudget()` and / or `responseSizeBudget()` when the entity's
 *    page legitimately needs a different ceiling.  Document the override with
 *    a short comment above the method so future maintainers understand why.
 *
 * ## Budget-tuning advice
 *
 * - Override `queryBudget()` with the smallest value that passes against the
 *   optimised page state (after EPICs 4–7).  Leave a margin of ~5 queries for
 *   framework overhead that varies between environments.
 * - Override `responseSizeBudget()` with `500_000` (500 KB) for pages that
 *   have no Livewire components, and up to `1_000_000` (1 MB) for pages that
 *   do.  Tighten the ceiling once you measure the actual size on a clean
 *   dataset.
 */
trait RendersShowPageUnderRealisticDataset
{
    /**
     * Maximum number of DB queries the show page may issue.
     * Full-table `get()` calls inflate this far above 25.
     * Override per entity with a short comment explaining the budget.
     */
    protected function queryBudget(): int
    {
        return 25;
    }

    /**
     * Maximum allowed response body size in bytes.
     * Livewire snapshots containing hundreds of option rows exceed this easily.
     * Override per entity with a short comment explaining the budget.
     */
    protected function responseSizeBudget(): int
    {
        return 1_000_000; // 1 MB
    }

    /**
     * PHP memory limit applied before the request is issued.
     * Mimics a constrained hosting environment; PHP catches the OOM as a
     * fatal and Pest reports it as a test failure.
     */
    protected function memoryLimit(): string
    {
        return '128M';
    }

    /**
     * Seed a production-like dataset and return the subject model whose show
     * page will be requested.
     *
     * Implementations must:
     * - Create the subject model via its factory.
     * - Create enough related rows (siblings, children, tags, translations,
     *   images, …) to surface N+1 and memory regressions.
     * - Keep counts deterministic; prefer `factory()->count(N)->create()`.
     */
    abstract protected function seedRealisticDataset(): Model;

    /**
     * Return the Laravel route name for the entity show action,
     * e.g. `'items.show'` or `'partners.show'`.
     */
    abstract protected function getShowRouteName(): string;

    /**
     * Runs the realistic-dataset show-page HTTP test.
     *
     * Seeding, query-log capture, and all three budget assertions are
     * performed in a single test method so the failure message points
     * directly to the offending entity.
     */
    public function test_show_page_renders_under_realistic_dataset(): void
    {
        ini_set('memory_limit', $this->memoryLimit());

        $subject = $this->seedRealisticDataset();

        DB::enableQueryLog();

        $response = $this->get(route($this->getShowRouteName(), $subject));

        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $responseSize = strlen($response->getContent());

        $response->assertOk();

        $this->assertLessThanOrEqual(
            $this->queryBudget(),
            $queryCount,
            "Show page issued {$queryCount} queries; budget is {$this->queryBudget()}.",
        );

        $this->assertLessThanOrEqual(
            $this->responseSizeBudget(),
            $responseSize,
            "Response body is {$responseSize} bytes; budget is {$this->responseSizeBudget()}.",
        );
    }
}

<?php

namespace Tests\Web\Pages;

use App\Models\Item;
use App\Models\Partner;
use App\Models\PartnerImage;
use App\Models\PartnerTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Web\Traits\AuthenticatesWebRequests;
use Tests\Web\Traits\RendersShowPageUnderRealisticDataset;

/**
 * Realistic-dataset HTTP test for GET /web/partners/{id}.
 *
 * Seeds a production-like number of related rows to surface memory-exhaustion
 * and query-count regressions that a single-record test would not catch.
 */
class PartnerShowRealisticDatasetTest extends TestCase
{
    use AuthenticatesWebRequests;
    use RefreshDatabase;
    use RendersShowPageUnderRealisticDataset;

    /**
     * 25 covers partner eager-loads (country, project, monumentItem,
     * partnerImages, translations.context, translations.language) plus
     * framework auth overhead.
     */
    protected function queryBudget(): int
    {
        return 25;
    }

    /**
     * 1 MB ceiling — translations and images do not embed large option
     * snapshots, but 1 MB gives headroom for future additions.
     */
    protected function responseSizeBudget(): int
    {
        return 1_000_000;
    }

    protected function getShowRouteName(): string
    {
        return 'partners.show';
    }

    protected function seedRealisticDataset(): Model
    {
        $subject = Partner::factory()->withCountry()->withProject()->create();

        // A monument item attached to this partner
        $monument = Item::factory()->create();
        $subject->update(['monument_item_id' => $monument->id]);

        // ≥ 200 sibling partners to verify no full-table load
        Partner::factory()->count(200)->create();

        // ≥ 10 translations attached to the subject
        PartnerTranslation::factory()->count(10)->create(['partner_id' => $subject->id]);

        // ≥ 5 images attached to the subject
        PartnerImage::factory()->count(5)->forPartner($subject)->create();

        return $subject;
    }
}

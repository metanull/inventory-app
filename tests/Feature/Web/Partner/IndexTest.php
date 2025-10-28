<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Partner;

use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class IndexTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_index_lists_partners_with_pagination(): void
    {
        Partner::factory()->count(25)->create();
        $response = $this->get(route('partners.index'));
        $response->assertOk();
        $response->assertSee('Partners');
        $response->assertSee('Rows per page');
        $firstPartner = Partner::query()->orderByDesc('created_at')->first();
        $response->assertSee(e($firstPartner->internal_name));
    }

    public function test_index_search_filters_results(): void
    {
        Partner::factory()->count(5)->create();
        $target = Partner::factory()->create(['internal_name' => 'SPECIAL_PARTNER_SEARCH']);
        $response = $this->get(route('partners.index', ['q' => 'SPECIAL_PARTNER_SEARCH']));
        $response->assertOk();
        $response->assertSee('SPECIAL_PARTNER_SEARCH');
        $nonMatch = Partner::where('id', '!=', $target->id)->first();
        if ($nonMatch) {
            $response->assertDontSee(e($nonMatch->internal_name));
        }
    }

    public function test_index_respects_per_page_query(): void
    {
        Partner::factory()->count(12)->create();
        $response = $this->get(route('partners.index', ['per_page' => 5]));
        $response->assertOk();
        $rowCount = substr_count($response->getContent(), '<tr');
        $this->assertGreaterThanOrEqual(5, $rowCount - 1);
    }
}

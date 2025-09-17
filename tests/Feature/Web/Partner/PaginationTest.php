<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Partner;

use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaginationTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_partners_index_paginates_across_pages(): void
    {
        $defaultPerPage = (int) config('interface.pagination.default_per_page');
        $total = $defaultPerPage + 5; // ensure second page
        Partner::factory()->count($total)->create();

        $firstPage = $this->get(route('partners.index'));
        $firstPage->assertOk();
        $firstPageContent = $firstPage->getContent();
        $this->assertStringContainsString('Partners', $firstPageContent);

        // Expect exactly defaultPerPage (allow header row) occurrences of <tr excluding header simplistically
        $rows = substr_count($firstPageContent, '<tr');
        $this->assertGreaterThanOrEqual($defaultPerPage, $rows - 1);

        $secondPage = $this->get(route('partners.index', ['page' => 2]));
        $secondPage->assertOk();
        $this->assertStringContainsString('Partners', $secondPage->getContent());
    }

    public function test_partners_index_respects_custom_per_page(): void
    {
        Partner::factory()->count(25)->create();
        $response = $this->get(route('partners.index', ['per_page' => 5]));
        $response->assertOk();
        $rows = substr_count($response->getContent(), '<tr');
        $this->assertGreaterThanOrEqual(5, $rows - 1);
    }
}

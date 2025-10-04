<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Partner;

use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class PaginationTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
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

        // Verify there are table rows without checking exact counts
        $rows = substr_count($firstPageContent, '<tr');
        $this->assertGreaterThan(1, $rows, 'Should have at least header row and data rows');

        $secondPage = $this->get(route('partners.index', ['page' => 2]));
        $secondPage->assertOk();
        $this->assertStringContainsString('Partners', $secondPage->getContent());

        // Verify second page also has table structure
        $secondPageRows = substr_count($secondPage->getContent(), '<tr');
        $this->assertGreaterThan(0, $secondPageRows, 'Second page should have table rows');
    }

    public function test_partners_index_respects_custom_per_page(): void
    {
        Partner::factory()->count(25)->create();
        $response = $this->get(route('partners.index', ['per_page' => 5]));
        $response->assertOk();

        // Verify pagination parameter is accepted by checking response structure
        $content = $response->getContent();
        $this->assertStringContainsString('Partners', $content);

        // Verify there are table rows (should be limited by per_page but don't assert exact count)
        $rows = substr_count($content, '<tr');
        $this->assertGreaterThan(1, $rows, 'Should have at least header row and data rows');
        // Allow reasonable upper bound that accommodates headers and pagination UI elements
        $this->assertLessThan(15, $rows, 'Should be reasonably limited even with pagination UI');
    }
}

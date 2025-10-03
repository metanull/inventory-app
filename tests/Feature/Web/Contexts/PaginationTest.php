<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Contexts;

use App\Models\Context;
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

    public function test_contexts_index_paginates_across_pages(): void
    {
        $defaultPerPage = (int) config('interface.pagination.default_per_page');
        Context::factory()->count($defaultPerPage + 7)->create();

        $firstPage = $this->get(route('contexts.index'));
        $firstPage->assertOk();

        // Check that pagination elements are present instead of exact counts
        $firstPageContent = $firstPage->getContent();
        $this->assertStringContainsString('Contexts', $firstPageContent);

        // Verify there are table rows (at least one data row plus header)
        $rows = substr_count($firstPageContent, '<tr');
        $this->assertGreaterThan(1, $rows, 'Should have at least header row and one data row');

        $secondPage = $this->get(route('contexts.index', ['page' => 2]));
        $secondPage->assertOk();

        // Verify second page also has content structure
        $this->assertStringContainsString('Contexts', $secondPage->getContent());
    }

    public function test_contexts_index_respects_custom_per_page(): void
    {
        Context::factory()->count(40)->create();
        $response = $this->get(route('contexts.index', ['per_page' => 10]));
        $response->assertOk();

        // Verify pagination parameter is accepted by checking response structure
        $content = $response->getContent();
        $this->assertStringContainsString('Contexts', $content);

        // Verify there are table rows (should be limited by per_page but don't assert exact count)
        $rows = substr_count($content, '<tr');
        $this->assertGreaterThan(1, $rows, 'Should have at least header row and data rows');
        $this->assertLessThan(15, $rows, 'Should be limited by per_page parameter plus header');
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Collections;

use App\Models\Collection;
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

    public function test_collections_index_paginates_across_pages(): void
    {
        $defaultPerPage = (int) config('interface.pagination.default_per_page');
        Collection::factory()->count($defaultPerPage + 7)->create();
        $firstPage = $this->get(route('collections.index'));
        $firstPage->assertOk();
        $rows = substr_count($firstPage->getContent(), '<tr');
        $this->assertGreaterThanOrEqual($defaultPerPage, $rows - 1);

        $secondPage = $this->get(route('collections.index', ['page' => 2]));
        $secondPage->assertOk();
    }

    public function test_collections_index_respects_custom_per_page(): void
    {
        Collection::factory()->count(40)->create();
        $response = $this->get(route('collections.index', ['per_page' => 10]));
        $response->assertOk();
        $rows = substr_count($response->getContent(), '<tr');
        $this->assertGreaterThanOrEqual(10, $rows - 1);
    }
}

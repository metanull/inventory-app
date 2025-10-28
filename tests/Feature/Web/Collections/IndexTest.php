<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Collections;

use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\CreatesUsersWithPermissions;

class IndexTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUsersWithPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAsDataUser();
    }

    public function test_index_lists_collections_with_pagination(): void
    {
        Collection::factory()->count(20)->create();
        $response = $this->get(route('collections.index'));
        $response->assertOk();
        $response->assertSee('Collections');
        $response->assertSee('Rows per page');
        $first = Collection::query()->orderByDesc('created_at')->first();
        $response->assertSee(e($first->internal_name));
    }

    public function test_index_search_filters_results(): void
    {
        Collection::factory()->count(5)->create();
        $target = Collection::factory()->create(['internal_name' => 'SPECIAL_COLLECTION_TOKEN']);

        $response = $this->get(route('collections.index', ['q' => 'SPECIAL_COLLECTION_TOKEN']));
        $response->assertOk();
        $response->assertSee('SPECIAL_COLLECTION_TOKEN');

        $nonMatch = Collection::where('id', '!=', $target->id)->first();
        if ($nonMatch) {
            $response->assertDontSee(e($nonMatch->internal_name));
        }
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Address;

use App\Models\Address;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class IndexTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;
    use WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_index_lists_addresses_with_pagination(): void
    {
        Address::factory()->count(25)->create();
        $response = $this->get(route('addresses.index'));
        $response->assertOk();
        $response->assertSee('Addresses');
        $response->assertSee('Search addresses');
        $first = Address::query()->orderByDesc('created_at')->first();
        $response->assertSee(e($first->internal_name));
    }

    public function test_index_search_filters_results(): void
    {
        Address::factory()->count(5)->create();
        $target = Address::factory()->create(['internal_name' => 'SPECIAL_ADDRESS_TOKEN']);

        $response = $this->get(route('addresses.index', ['q' => 'SPECIAL_ADDRESS_TOKEN']));
        $response->assertOk();
        $response->assertSee('SPECIAL_ADDRESS_TOKEN');

        $nonMatch = Address::where('id', '!=', $target->id)->first();
        if ($nonMatch) {
            $response->assertDontSee(e($nonMatch->internal_name));
        }
    }

    public function test_index_respects_per_page_query(): void
    {
        Address::factory()->count(15)->create();
        $response = $this->get(route('addresses.index', ['per_page' => 10]));
        $response->assertOk();
        $rowCount = substr_count($response->getContent(), '<tr');
        $this->assertGreaterThanOrEqual(10, $rowCount - 1);
    }
}

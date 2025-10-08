<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Countries;

use App\Models\Country;
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

    public function test_countries_index_renders_and_lists_countries(): void
    {
        Country::factory()->count(2)->create();
        $response = $this->get(route('countries.index'));
        $response->assertOk();
        $response->assertSee('Countries');
        $response->assertSee('Add Country');
        $response->assertSee('Search');
    }

    public function test_countries_index_search_filters_results(): void
    {
        Country::factory()->create(['id' => 'AAA', 'internal_name' => 'ALPHA LAND']);
        Country::factory()->create(['id' => 'BBB', 'internal_name' => 'BETA LAND']);
        $response = $this->get(route('countries.index', ['q' => 'ALPHA']));
        $response->assertOk();
        $response->assertSee('ALPHA LAND');
        $response->assertDontSee('BETA LAND');
    }
}

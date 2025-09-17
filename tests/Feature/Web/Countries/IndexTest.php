<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Countries;

use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_countries_index_renders_and_lists_countries(): void
    {
        $user = User::factory()->create();
        Country::factory()->count(2)->create();

        $this->actingAs($user);
        $response = $this->get(route('countries.index'));
        $response->assertOk();
        $response->assertSee('Countries');
        $response->assertSee('Add Country');
        $response->assertSee('Search');
    }

    public function test_countries_index_search_filters_results(): void
    {
        $user = User::factory()->create();
        Country::factory()->create(['id' => 'AAA', 'internal_name' => 'ALPHA LAND']);
        Country::factory()->create(['id' => 'BBB', 'internal_name' => 'BETA LAND']);

        $this->actingAs($user);
        $response = $this->get(route('countries.index', ['q' => 'ALPHA']));
        $response->assertOk();
        $response->assertSee('ALPHA LAND');
        $response->assertDontSee('BETA LAND');
    }
}

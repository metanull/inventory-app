<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Countries;

use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\RequiresDataPermissions;

class ShowTest extends TestCase
{
    use RefreshDatabase;
    use RequiresDataPermissions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actAsRegularUser();
    }

    public function test_country_show_page_displays_core_fields(): void
    {
        $country = Country::factory()->create(['id' => 'XYZ', 'backward_compatibility' => 'OL']);
        $response = $this->get(route('countries.show', $country));
        $response->assertOk();
        $response->assertSee($country->internal_name);
        $response->assertSee('Legacy: OL');
        $response->assertSee('Information');
    }
}

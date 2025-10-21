<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Address;

use App\Models\Address;
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

    public function test_show_displays_core_fields(): void
    {
        $country = Country::factory()->create(['internal_name' => 'United States']);
        $address = Address::factory()->create([
            'internal_name' => 'Head Office',
            'country_id' => $country->id,
        ]);

        $response = $this->get(route('addresses.show', $address));
        $response->assertOk();
        $response->assertSee('Head Office');
        $response->assertSee('United States');
    }
}

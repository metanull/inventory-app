<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Countries;

use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_country_show_page_displays_core_fields(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $country = Country::factory()->create(['id' => 'XYZ', 'backward_compatibility' => 'OL']);

        $this->actingAs($user);
        $response = $this->get(route('countries.show', $country));
        $response->assertOk();
        $response->assertSee($country->internal_name);
        $response->assertSee('Legacy: OL');
        $response->assertSee('Information');
    }
}

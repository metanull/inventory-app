<?php

declare(strict_types=1);

namespace Tests\Feature\Web\Countries;

use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_countries_index(): void
    {
        $response = $this->get(route('countries.index'));
        $response->assertRedirect();
    }

    public function test_guest_cannot_view_country(): void
    {
        $country = Country::factory()->create();
        $response = $this->get(route('countries.show', $country));
        $response->assertRedirect();
    }
}

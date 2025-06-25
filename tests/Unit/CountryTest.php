<?php

namespace Tests\Unit;

use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CountryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Factory: Assert the Country factory creates the expected data.
     */
    public function test_factory()
    {
        $country = Country::factory()->create();
        $this->assertInstanceOf(Country::class, $country);
        $this->assertNotEmpty($country->id);
        $this->assertNotEmpty($country->internal_name);
        $this->assertNotEmpty($country->backward_compatibility);
    }

    /**
     * Factory: Assert the Country factory creates a row in the database.
     */
    public function test_factory_creates_a_row_in_database()
    {
        $country = Country::factory()->create();
        $this->assertDatabaseHas('countries', [
            'id' => $country->id,
            'internal_name' => $country->internal_name,
            'backward_compatibility' => $country->backward_compatibility,
        ]);
        $this->assertDatabaseCount('countries', 1);
    }
}

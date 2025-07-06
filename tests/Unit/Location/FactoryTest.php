<?php

namespace Tests\Unit\Location;

use App\Models\Country;
use App\Models\Language;
use App\Models\Location;
use App\Models\LocationLanguage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_location_factory_creates_valid_location(): void
    {
        // Create languages and countries first
        Language::factory(3)->create();
        Country::factory(2)->create();

        $location = Location::factory()->create();

        $this->assertDatabaseHas('locations', [
            'id' => $location->id,
            'internal_name' => $location->internal_name,
            'country_id' => $location->country_id,
        ]);

        // Check that the location has UUID
        $this->assertIsString($location->id);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $location->id);

        // Check that internal_name is unique
        $this->assertNotEmpty($location->internal_name);

        // Check that country_id exists in countries table
        $this->assertDatabaseHas('countries', ['id' => $location->country_id]);

        // Load the languages relationship manually for testing
        $location->load('languages');

        // Check that languages relationship is loaded
        $this->assertTrue($location->relationLoaded('languages'));
        $this->assertGreaterThan(0, $location->languages->count());

        // Check that each language relationship has the required pivot data
        foreach ($location->languages as $language) {
            $this->assertNotEmpty($language->pivot->name);
            $this->assertDatabaseHas('location_language', [
                'location_id' => $location->id,
                'language_id' => $language->id,
                'name' => $language->pivot->name,
            ]);
        }
    }

    public function test_location_factory_creates_multiple_locations_with_unique_internal_names(): void
    {
        Language::factory(3)->create();
        Country::factory(2)->create();

        $locations = Location::factory(5)->create();

        $this->assertCount(5, $locations);

        $internalNames = $locations->pluck('internal_name')->toArray();
        $this->assertCount(5, array_unique($internalNames), 'All internal names should be unique');
    }

    public function test_location_has_country_relationship(): void
    {
        Language::factory(3)->create();
        $country = Country::factory()->create();

        $location = Location::factory()->create(['country_id' => $country->id]);

        $this->assertEquals($country->id, $location->country->id);
        $this->assertEquals($country->internal_name, $location->country->internal_name);
    }

    public function test_location_has_languages_relationship(): void
    {
        $languages = Language::factory(3)->create();
        $country = Country::factory()->create();

        $location = Location::factory()->create(['country_id' => $country->id]);

        $this->assertGreaterThan(0, $location->languages->count());
        $this->assertInstanceOf(LocationLanguage::class, $location->languages->first()->pivot);
    }
}

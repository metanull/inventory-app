<?php

namespace Tests\Unit\Location;

use App\Models\Country;
use App\Models\Location;
use App\Models\LocationTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_location_factory_creates_valid_location(): void
    {
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

        // Check that translations relationship exists and is accessible
        $this->assertGreaterThan(0, $location->translations->count());

        // Check that each translation has the required data
        foreach ($location->translations as $translation) {
            $this->assertNotEmpty($translation->name);
            $this->assertDatabaseHas('location_translations', [
                'location_id' => $location->id,
                'language_id' => $translation->language_id,
                'name' => $translation->name,
            ]);
        }
    }

    public function test_location_factory_creates_multiple_locations_with_unique_internal_names(): void
    {
        $locations = Location::factory(5)->create();

        $this->assertCount(5, $locations);

        $internalNames = $locations->pluck('internal_name')->toArray();
        $this->assertCount(5, array_unique($internalNames), 'All internal names should be unique');
    }

    public function test_location_has_country_relationship(): void
    {
        $country = Country::factory()->create();

        $location = Location::factory()->create(['country_id' => $country->id]);

        $this->assertEquals($country->id, $location->country->id);
        $this->assertEquals($country->internal_name, $location->country->internal_name);
    }

    public function test_location_has_translations_relationship(): void
    {
        $location = Location::factory()->create();

        $this->assertGreaterThan(0, $location->translations->count());
        $this->assertInstanceOf(LocationTranslation::class, $location->translations->first());
    }
}

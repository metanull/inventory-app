<?php

namespace Tests\Unit\Address;

use App\Models\Address;
use App\Models\AddressLanguage;
use App\Models\Country;
use App\Models\Language;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_address_factory_creates_valid_address(): void
    {
        // Create languages and countries first
        Language::factory(3)->create();
        Country::factory(2)->create();

        $address = Address::factory()->create();

        $this->assertDatabaseHas('addresses', [
            'id' => $address->id,
            'internal_name' => $address->internal_name,
            'country_id' => $address->country_id,
        ]);

        // Check that the address has UUID
        $this->assertIsString($address->id);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $address->id);

        // Check that internal_name is unique
        $this->assertNotEmpty($address->internal_name);

        // Check that country_id exists in countries table
        $this->assertDatabaseHas('countries', ['id' => $address->country_id]);

        // Load the languages relationship manually for testing
        $address->load('languages');

        // Check that languages relationship is loaded
        $this->assertTrue($address->relationLoaded('languages'));
        $this->assertGreaterThan(0, $address->languages->count());

        // Check that each language relationship has the required pivot data
        foreach ($address->languages as $language) {
            $this->assertNotEmpty($language->pivot->address);
            $this->assertDatabaseHas('address_language', [
                'address_id' => $address->id,
                'language_id' => $language->id,
                'address' => $language->pivot->address,
            ]);
        }
    }

    public function test_address_factory_creates_multiple_addresses_with_unique_internal_names(): void
    {
        Language::factory(3)->create();
        Country::factory(2)->create();

        $addresses = Address::factory(5)->create();

        $this->assertCount(5, $addresses);

        $internalNames = $addresses->pluck('internal_name')->toArray();
        $this->assertCount(5, array_unique($internalNames), 'All internal names should be unique');
    }

    public function test_address_has_country_relationship(): void
    {
        Language::factory(3)->create();
        $country = Country::factory()->create();

        $address = Address::factory()->create(['country_id' => $country->id]);

        $this->assertEquals($country->id, $address->country->id);
        $this->assertEquals($country->internal_name, $address->country->internal_name);
    }

    public function test_address_has_languages_relationship(): void
    {
        $languages = Language::factory(3)->create();
        $country = Country::factory()->create();

        $address = Address::factory()->create(['country_id' => $country->id]);

        $this->assertGreaterThan(0, $address->languages->count());
        $this->assertInstanceOf(AddressLanguage::class, $address->languages->first()->pivot);

        // Check that address and description are properly set
        foreach ($address->languages as $language) {
            $this->assertNotEmpty($language->pivot->address);
            // Description can be null, so we just check it exists as an attribute
            $this->assertArrayHasKey('description', $language->pivot->getAttributes());
        }
    }
}

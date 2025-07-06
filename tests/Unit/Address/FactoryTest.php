<?php

namespace Tests\Unit\Address;

use App\Models\Address;
use App\Models\AddressTranslation;
use App\Models\Country;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_address_factory_creates_valid_address(): void
    {
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

        // Check that translations relationship exists and is accessible
        $this->assertGreaterThan(0, $address->translations->count());

        // Check that each translation has the required data
        foreach ($address->translations as $translation) {
            $this->assertNotEmpty($translation->address);
            $this->assertDatabaseHas('address_translations', [
                'address_id' => $address->id,
                'language_id' => $translation->language_id,
                'address' => $translation->address,
            ]);
        }
    }

    public function test_address_factory_creates_multiple_addresses_with_unique_internal_names(): void
    {
        $addresses = Address::factory(5)->create();

        $this->assertCount(5, $addresses);

        $internalNames = $addresses->pluck('internal_name')->toArray();
        $this->assertCount(5, array_unique($internalNames), 'All internal names should be unique');
    }

    public function test_address_has_country_relationship(): void
    {
        $country = Country::factory()->create();

        $address = Address::factory()->create(['country_id' => $country->id]);

        $this->assertEquals($country->id, $address->country->id);
        $this->assertEquals($country->internal_name, $address->country->internal_name);
    }

    public function test_address_has_translations_relationship(): void
    {
        $address = Address::factory()->create();

        $this->assertGreaterThan(0, $address->translations->count());
        $this->assertInstanceOf(AddressTranslation::class, $address->translations->first());

        // Check that address and description are properly set
        foreach ($address->translations as $translation) {
            $this->assertNotEmpty($translation->address);
            // Description can be null, so we just check it exists as an attribute
            $this->assertArrayHasKey('description', $translation->getAttributes());
        }
    }
}

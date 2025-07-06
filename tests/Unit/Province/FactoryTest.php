<?php

namespace Tests\Unit\Province;

use App\Models\Country;
use App\Models\Language;
use App\Models\Province;
use App\Models\ProvinceLanguage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FactoryTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_province_factory_creates_valid_province(): void
    {
        $province = Province::factory()->create();

        $this->assertDatabaseHas('provinces', [
            'id' => $province->id,
            'internal_name' => $province->internal_name,
            'country_id' => $province->country_id,
        ]);

        // Check that the province has UUID
        $this->assertIsString($province->id);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $province->id);

        // Check that internal_name is unique
        $this->assertNotEmpty($province->internal_name);

        // Check that country_id exists in countries table
        $this->assertDatabaseHas('countries', ['id' => $province->country_id]);

        // Check that languages relationship exists and is accessible
        $this->assertGreaterThan(0, $province->languages->count());

        // Check that each language relationship has the required pivot data
        foreach ($province->languages as $language) {
            $this->assertNotEmpty($language->pivot->name);
            $this->assertDatabaseHas('province_language', [
                'province_id' => $province->id,
                'language_id' => $language->id,
                'name' => $language->pivot->name,
            ]);
        }
    }

    public function test_province_factory_creates_multiple_provinces_with_unique_internal_names(): void
    {
        $provinces = Province::factory(5)->create();

        $this->assertCount(5, $provinces);

        $internalNames = $provinces->pluck('internal_name')->toArray();
        $this->assertCount(5, array_unique($internalNames), 'All internal names should be unique');
    }

    public function test_province_has_country_relationship(): void
    {
        $country = Country::factory()->create();

        $province = Province::factory()->create(['country_id' => $country->id]);

        $this->assertEquals($country->id, $province->country->id);
        $this->assertEquals($country->internal_name, $province->country->internal_name);
    }

    public function test_province_has_languages_relationship(): void
    {
        $province = Province::factory()->create();

        $this->assertGreaterThan(0, $province->languages->count());
        $this->assertInstanceOf(ProvinceLanguage::class, $province->languages->first()->pivot);
    }
}

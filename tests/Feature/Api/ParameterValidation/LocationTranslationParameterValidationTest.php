<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Language;
use App\Models\Location;
use App\Models\LocationTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive parameter validation tests for LocationTranslation API endpoints
 */
class LocationTranslationParameterValidationTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    // INDEX ENDPOINT TESTS
    public function test_index_accepts_valid_pagination_parameters()
    {
        $location = Location::factory()->create();
        LocationTranslation::factory()->count(18)->create(['location_id' => $location->id]);

        $response = $this->getJson(route('location-translation.index', [
            'page' => 2,
            'per_page' => 9,
        ]));

        $response->assertOk();
        $response->assertJsonPath('meta.current_page', 2);
        $response->assertJsonPath('meta.per_page', 9);
    }

    public function test_index_accepts_valid_include_parameters()
    {
        $location = Location::factory()->create();
        LocationTranslation::factory()->count(4)->create(['location_id' => $location->id]);

        $response = $this->getJson(route('location-translation.index', [
            'include' => 'location,language',
        ]));

        $response->assertOk();
    }

    public function test_index_rejects_invalid_include_parameters()
    {
        $location = Location::factory()->create();
        LocationTranslation::factory()->count(2)->create(['location_id' => $location->id]);

        $response = $this->getJson(route('location-translation.index', [
            'include' => 'invalid_relation,fake_location,non_existent',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['include']);
    }

    public function test_index_rejects_unexpected_query_parameters_currently()
    {
        $location = Location::factory()->create();
        LocationTranslation::factory()->count(3)->create(['location_id' => $location->id]);

        $response = $this->getJson(route('location-translation.index', [
            'page' => 1,
            'include' => 'location',
            'filter_by_province' => 'ABC', // Not implemented
            'location_type' => 'city', // Not implemented
            'population_range' => 'large', // Not implemented
            'admin_access' => true,
            'debug_translations' => true,
            'export_format' => 'xml',
            'bulk_operation' => 'geocode_all',
        ]));

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['filter_by_province']);
    }

    // SHOW ENDPOINT TESTS
    public function test_show_accepts_valid_include_parameters()
    {
        $location = Location::factory()->create();
        $translation = LocationTranslation::factory()->create(['location_id' => $location->id]);

        $response = $this->getJson(route('location-translation.show', $translation).'?include=location,language');

        $response->assertOk();
    }

    public function test_show_rejects_unexpected_query_parameters_currently()
    {
        $location = Location::factory()->create();
        $translation = LocationTranslation::factory()->create(['location_id' => $location->id]);

        $response = $this->getJson(route('location-translation.show', $translation).'?include=location&show_coordinates=true&geographic_details=full');

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['show_coordinates']);
    }

    // STORE ENDPOINT TESTS
    public function test_store_validates_required_fields()
    {
        $response = $this->postJson(route('location-translation.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['location_id', 'language_code']);
    }

    public function test_store_validates_location_id_exists()
    {
        $language = Language::factory()->create();

        $response = $this->postJson(route('location-translation.store'), [
            'location_id' => 'non-existent-uuid',
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['location_id']);
    }

    public function test_store_validates_language_code_exists()
    {
        $location = Location::factory()->create();

        $response = $this->postJson(route('location-translation.store'), [
            'location_id' => $location->id,
            'language_code' => 'xyz', // Invalid language code
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_code']);
    }

    public function test_store_validates_unique_combination()
    {
        $location = Location::factory()->create();
        $language = Language::factory()->create();
        LocationTranslation::factory()->create([
            'location_id' => $location->id,
            'language_code' => $language->code,
        ]);

        $response = $this->postJson(route('location-translation.store'), [
            'location_id' => $location->id,
            'language_code' => $language->code, // Duplicate combination
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['location_id']);
    }

    public function test_store_validates_location_id_uuid_format()
    {
        $language = Language::factory()->create();

        $response = $this->postJson(route('location-translation.store'), [
            'location_id' => 'not-a-uuid',
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['location_id']);
    }

    public function test_store_validates_language_code_format()
    {
        $location = Location::factory()->create();

        $response = $this->postJson(route('location-translation.store'), [
            'location_id' => $location->id,
            'language_code' => 'toolong', // Should be 3 characters
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_code']);
    }

    public function test_store_prohibits_id_field()
    {
        $location = Location::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('location-translation.store'), [
            'id' => 'some-uuid', // Should be prohibited
            'location_id' => $location->id,
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_store_accepts_valid_data()
    {
        $location = Location::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('location-translation.store'), [
            'location_id' => $location->id,
            'language_code' => $language->code,
            'name' => 'Translated Location Name',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.location_id', $location->id);
        $response->assertJsonPath('data.language_code', $language->code);
    }

    public function test_store_rejects_unexpected_request_parameters_currently()
    {
        $location = Location::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('location-translation.store'), [
            'location_id' => $location->id,
            'language_code' => $language->code,
            'name' => 'Test Location Translation',
            'unexpected_field' => 'should_be_rejected',
            'local_name' => 'Local City Name', // Not implemented
            'historical_name' => 'Old Name', // Not implemented
            'nickname' => 'City Nickname', // Not implemented
            'founded_date' => '1850', // Not implemented
            'admin_created' => true,
            'malicious_script' => '<script>location.href="evil.com"</script>',
            'sql_injection' => "'; DROP TABLE location_translations; --",
            'privilege_escalation' => 'geographic_admin',
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_validates_unique_combination_on_change()
    {
        $location1 = Location::factory()->create();
        $location2 = Location::factory()->create();
        $language = Language::factory()->create();

        $translation1 = LocationTranslation::factory()->create([
            'location_id' => $location1->id,
            'language_code' => $language->code,
        ]);

        LocationTranslation::factory()->create([
            'location_id' => $location2->id,
            'language_code' => $language->code,
        ]);

        $response = $this->putJson(route('location-translation.update', $translation1), [
            'location_id' => $location2->id, // Would create duplicate
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['location_id']);
    }

    public function test_update_prohibits_id_modification()
    {
        $location = Location::factory()->create();
        $translation = LocationTranslation::factory()->create(['location_id' => $location->id]);

        $response = $this->putJson(route('location-translation.update', $translation), [
            'id' => 'new-uuid', // Should be prohibited
            'location_id' => $location->id,
            'language_code' => $translation->language_code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_update_accepts_same_combination()
    {
        $location = Location::factory()->create();
        $translation = LocationTranslation::factory()->create(['location_id' => $location->id]);

        $response = $this->putJson(route('location-translation.update', $translation), [
            'location_id' => $translation->location_id, // Same combination
            'language_code' => $translation->language_code,
            'name' => 'Updated Location Name',
        ]);

        $response->assertOk();
    }

    public function test_update_rejects_unexpected_request_parameters_currently()
    {
        $location = Location::factory()->create();
        $translation = LocationTranslation::factory()->create(['location_id' => $location->id]);

        $response = $this->putJson(route('location-translation.update', $translation), [
            'location_id' => $translation->location_id,
            'language_code' => $translation->language_code,
            'name' => 'Updated Location Translation',
            'unexpected_field' => 'should_be_rejected',
            'change_status' => 'verified',
            'update_coordinates' => 'precise',
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // EDGE CASE TESTS
    public function test_handles_unicode_characters_in_translation_fields()
    {
        $location = Location::factory()->create();

        $unicodeLocationNames = [
            'Ville française',
            'Город русский',
            '市日本語',
            'مدينة عربية',
            'Ciudad española',
            'Città italiana',
            'Miasto polskie',
            'Πόλη ελληνική',
            'By dansk',
            'Város magyar',
        ];

        foreach ($unicodeLocationNames as $index => $name) {
            $newLanguage = Language::factory()->create(['code' => sprintf('%03d', $index)]);

            $response = $this->postJson(route('location-translation.store'), [
                'location_id' => $location->id,
                'language_code' => $newLanguage->code,
                'name' => $name,
            ]);

            $response->assertCreated(); // Should handle Unicode gracefully
        }
    }

    public function test_handles_empty_and_null_optional_fields()
    {
        $location = Location::factory()->create();
        $translation = LocationTranslation::factory()->create(['location_id' => $location->id]);

        $testCases = [
            ['name' => null],
            ['name' => ''],
            ['name' => '   '], // Whitespace only
        ];

        foreach ($testCases as $data) {
            $updateData = array_merge([
                'location_id' => $translation->location_id,
                'language_code' => $translation->language_code,
            ], $data);

            $response = $this->putJson(route('location-translation.update', $translation), $updateData);

            // Should handle gracefully
            $this->assertContains($response->status(), [200, 422]);
        }
    }

    public function test_handles_very_long_translation_content()
    {
        $location = Location::factory()->create();
        $language = Language::factory()->create();

        $veryLongName = str_repeat('Very Long Location Name With Extended Geographic Description And Historical Context ', 25);

        $response = $this->postJson(route('location-translation.store'), [
            'location_id' => $location->id,
            'language_code' => $language->code,
            'name' => $veryLongName,
        ]);

        // Should handle gracefully
        $this->assertContains($response->status(), [201, 422]);
    }

    public function test_handles_array_injection_attempts()
    {
        $location = Location::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('location-translation.store'), [
            'location_id' => ['array' => 'instead_of_uuid'],
            'language_code' => ['malicious' => 'array'],
            'name' => ['injection' => 'attempt'],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['location_id']);
    }

    public function test_pagination_with_many_translations()
    {
        $location = Location::factory()->create();
        LocationTranslation::factory()->count(60)->create(['location_id' => $location->id]);

        $testCases = [
            ['page' => 1, 'per_page' => 20],
            ['page' => 2, 'per_page' => 25],
            ['page' => 1, 'per_page' => 100], // Maximum
        ];

        foreach ($testCases as $params) {
            $response = $this->getJson(route('location-translation.index', $params));
            $response->assertOk();
        }

        // Test invalid pagination
        $invalidCases = [
            ['page' => 0],
            ['per_page' => 0],
            ['per_page' => 101],
            ['page' => -1],
        ];

        foreach ($invalidCases as $params) {
            $response = $this->getJson(route('location-translation.index', $params));
            $response->assertUnprocessable();
        }
    }

    public function test_handles_special_characters_in_translation_content()
    {
        $location = Location::factory()->create();

        $specialCharNames = [
            'Location "Saint-Jean-de-Luz"',
            "Location 'Aix-en-Provence'",
            'Location & Surroundings',
            'Location: Downtown Area',
            'Location (Historic District)',
            'Location - Waterfront',
            'Location @ Coordinates',
            'Location #1',
            'Location 50% Urban',
            'Location $Commercial District',
            'Location *Historic Center',
            'Location +Metropolitan Area',
            'Location =Tourist Zone',
            'Location |Border Town',
        ];

        foreach ($specialCharNames as $index => $name) {
            $newLanguage = Language::factory()->create(['code' => sprintf('l%02d', $index)]);

            $response = $this->postJson(route('location-translation.store'), [
                'location_id' => $location->id,
                'language_code' => $newLanguage->code,
                'name' => $name,
            ]);

            // Should handle gracefully
            $this->assertContains($response->status(), [201, 422]);
        }
    }

    public function test_handles_location_translation_workflow()
    {
        $location = Location::factory()->create();
        $english = Language::factory()->create(['code' => 'eng']);
        $french = Language::factory()->create(['code' => 'fra']);
        $spanish = Language::factory()->create(['code' => 'spa']);

        // Create translations in different languages
        $languages = [$english, $french, $spanish];
        $names = ['Paris', 'Paris', 'París'];

        foreach ($languages as $index => $language) {
            $response = $this->postJson(route('location-translation.store'), [
                'location_id' => $location->id,
                'language_code' => $language->code,
                'name' => $names[$index],
            ]);

            $response->assertCreated();
        }

        // Verify all translations exist
        $indexResponse = $this->getJson(route('location-translation.index'));
        $indexResponse->assertOk();
        $indexResponse->assertJsonPath('meta.total', 3);
    }

    public function test_handles_city_type_location_name_variations()
    {
        $location = Location::factory()->create();

        $cityTypeNames = [
            'New York City',
            'Los Angeles',
            'Mexico City',
            'São Paulo',
            'Buenos Aires',
            'Rio de Janeiro',
            'Quebec City',
            'Salt Lake City',
            'Kansas City',
            'Oklahoma City',
            'Virginia Beach',
            'Baton Rouge',
            'Des Moines',
            'Las Vegas',
            'Saint Petersburg',
        ];

        foreach ($cityTypeNames as $index => $name) {
            $newLanguage = Language::factory()->create(['code' => sprintf('c%02d', $index)]);

            $response = $this->postJson(route('location-translation.store'), [
                'location_id' => $location->id,
                'language_code' => $newLanguage->code,
                'name' => $name,
            ]);

            $response->assertCreated(); // Should handle city name variations
        }
    }

    public function test_handles_geographic_location_name_variations()
    {
        $location = Location::factory()->create();

        $geographicNames = [
            'North Bay',
            'South Beach',
            'East Village',
            'West End',
            'Central Park',
            'Old Town',
            'New City',
            'Upper District',
            'Lower Valley',
            'Midtown',
            'Downtown',
            'Uptown',
            'Riverside',
            'Hillside',
            'Lakeside',
        ];

        foreach ($geographicNames as $index => $name) {
            $newLanguage = Language::factory()->create(['code' => sprintf('r%02d', $index)]);

            $response = $this->postJson(route('location-translation.store'), [
                'location_id' => $location->id,
                'language_code' => $newLanguage->code,
                'name' => $name,
            ]);

            $response->assertCreated(); // Should handle geographic name variations
        }
    }
}

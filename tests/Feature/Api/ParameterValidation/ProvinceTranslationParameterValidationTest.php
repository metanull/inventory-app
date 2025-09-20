<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Language;
use App\Models\Province;
use App\Models\ProvinceTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive parameter validation tests for ProvinceTranslation API endpoints
 */
class ProvinceTranslationParameterValidationTest extends TestCase
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
        $province = Province::factory()->create();
        ProvinceTranslation::factory()->count(12)->create(['province_id' => $province->id]);

        $response = $this->getJson(route('province-translation.index', [
            'page' => 3,
            'per_page' => 4,
        ]));

        $response->assertOk();
        $response->assertJsonPath('meta.current_page', 3);
        $response->assertJsonPath('meta.per_page', 4);
    }

    public function test_index_accepts_valid_include_parameters()
    {
        $province = Province::factory()->create();
        ProvinceTranslation::factory()->count(3)->create(['province_id' => $province->id]);

        $response = $this->getJson(route('province-translation.index', [
            'include' => 'province,language',
        ]));

        $response->assertOk();
    }

    public function test_index_rejects_invalid_include_parameters()
    {
        $province = Province::factory()->create();
        ProvinceTranslation::factory()->count(2)->create(['province_id' => $province->id]);

        $response = $this->getJson(route('province-translation.index', [
            'include' => 'invalid_relation,fake_province,non_existent',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['include']);
    }

    public function test_index_rejects_unexpected_query_parameters_currently()
    {
        $province = Province::factory()->create();
        ProvinceTranslation::factory()->count(2)->create(['province_id' => $province->id]);

        $response = $this->getJson(route('province-translation.index', [
            'page' => 1,
            'include' => 'province',
            'filter_by_country' => 'FR', // Not implemented
            'region_type' => 'state', // Not implemented
            'administrative_level' => '1', // Not implemented
            'admin_access' => true,
            'debug_translations' => true,
            'export_format' => 'json',
            'bulk_operation' => 'normalize_all',
        ]));

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['filter_by_country']);
    }

    // SHOW ENDPOINT TESTS
    public function test_show_accepts_valid_include_parameters()
    {
        $province = Province::factory()->create();
        $translation = ProvinceTranslation::factory()->create(['province_id' => $province->id]);

        $response = $this->getJson(route('province-translation.show', $translation).'?include=province,language');

        $response->assertOk();
    }

    public function test_show_rejects_unexpected_query_parameters_currently()
    {
        $province = Province::factory()->create();
        $translation = ProvinceTranslation::factory()->create(['province_id' => $province->id]);

        $response = $this->getJson(route('province-translation.show', $translation).'?include=province&show_geography=true&administrative_details=full');

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['show_geography']);
    }

    // STORE ENDPOINT TESTS
    public function test_store_validates_required_fields()
    {
        $response = $this->postJson(route('province-translation.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['province_id', 'language_code']);
    }

    public function test_store_validates_province_id_exists()
    {
        $language = Language::factory()->create();

        $response = $this->postJson(route('province-translation.store'), [
            'province_id' => 'non-existent-uuid',
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['province_id']);
    }

    public function test_store_validates_language_code_exists()
    {
        $province = Province::factory()->create();

        $response = $this->postJson(route('province-translation.store'), [
            'province_id' => $province->id,
            'language_code' => 'xyz', // Invalid language code
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_code']);
    }

    public function test_store_validates_unique_combination()
    {
        $province = Province::factory()->create();
        $language = Language::factory()->create();
        ProvinceTranslation::factory()->create([
            'province_id' => $province->id,
            'language_code' => $language->code,
        ]);

        $response = $this->postJson(route('province-translation.store'), [
            'province_id' => $province->id,
            'language_code' => $language->code, // Duplicate combination
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['province_id']);
    }

    public function test_store_validates_province_id_uuid_format()
    {
        $language = Language::factory()->create();

        $response = $this->postJson(route('province-translation.store'), [
            'province_id' => 'not-a-uuid',
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['province_id']);
    }

    public function test_store_validates_language_code_format()
    {
        $province = Province::factory()->create();

        $response = $this->postJson(route('province-translation.store'), [
            'province_id' => $province->id,
            'language_code' => 'toolong', // Should be 3 characters
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_code']);
    }

    public function test_store_prohibits_id_field()
    {
        $province = Province::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('province-translation.store'), [
            'id' => 'some-uuid', // Should be prohibited
            'province_id' => $province->id,
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_store_accepts_valid_data()
    {
        $province = Province::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('province-translation.store'), [
            'province_id' => $province->id,
            'language_code' => $language->code,
            'name' => 'Translated Province Name',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.province_id', $province->id);
        $response->assertJsonPath('data.language_code', $language->code);
    }

    public function test_store_rejects_unexpected_request_parameters_currently()
    {
        $province = Province::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('province-translation.store'), [
            'province_id' => $province->id,
            'language_code' => $language->code,
            'name' => 'Test Province Translation',
            'unexpected_field' => 'should_be_rejected',
            'official_name' => 'Official Province Name', // Not implemented
            'local_name' => 'Local Name', // Not implemented
            'abbreviation' => 'TP', // Not implemented
            'capital_city' => 'Capital', // Not implemented
            'admin_created' => true,
            'malicious_html' => '<iframe src="evil.com"></iframe>',
            'sql_injection' => "'; DROP TABLE province_translations; --",
            'privilege_escalation' => 'geographic_admin',
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_validates_unique_combination_on_change()
    {
        $province1 = Province::factory()->create();
        $province2 = Province::factory()->create();
        $language = Language::factory()->create();

        $translation1 = ProvinceTranslation::factory()->create([
            'province_id' => $province1->id,
            'language_code' => $language->code,
        ]);

        ProvinceTranslation::factory()->create([
            'province_id' => $province2->id,
            'language_code' => $language->code,
        ]);

        $response = $this->putJson(route('province-translation.update', $translation1), [
            'province_id' => $province2->id, // Would create duplicate
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['province_id']);
    }

    public function test_update_prohibits_id_modification()
    {
        $province = Province::factory()->create();
        $translation = ProvinceTranslation::factory()->create(['province_id' => $province->id]);

        $response = $this->putJson(route('province-translation.update', $translation), [
            'id' => 'new-uuid', // Should be prohibited
            'province_id' => $province->id,
            'language_code' => $translation->language_code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_update_accepts_same_combination()
    {
        $province = Province::factory()->create();
        $translation = ProvinceTranslation::factory()->create(['province_id' => $province->id]);

        $response = $this->putJson(route('province-translation.update', $translation), [
            'province_id' => $translation->province_id, // Same combination
            'language_code' => $translation->language_code,
            'name' => 'Updated Province Name',
        ]);

        $response->assertOk();
    }

    public function test_update_rejects_unexpected_request_parameters_currently()
    {
        $province = Province::factory()->create();
        $translation = ProvinceTranslation::factory()->create(['province_id' => $province->id]);

        $response = $this->putJson(route('province-translation.update', $translation), [
            'province_id' => $translation->province_id,
            'language_code' => $translation->language_code,
            'name' => 'Updated Province Translation',
            'unexpected_field' => 'should_be_rejected',
            'change_status' => 'verified',
            'update_boundaries' => 'expand',
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // EDGE CASE TESTS
    public function test_handles_unicode_characters_in_translation_fields()
    {
        $province = Province::factory()->create();

        $unicodeProvinceNames = [
            'Province française',
            'Провинция русская',
            '州日本語',
            'مقاطعة عربية',
            'Provincia española',
            'Provincia italiana',
            'Prowincja polska',
            'Επαρχία ελληνική',
            'Provins dansk',
            'Tartomány magyar',
        ];

        foreach ($unicodeProvinceNames as $index => $name) {
            $newLanguage = Language::factory()->create(['code' => sprintf('%03d', $index)]);

            $response = $this->postJson(route('province-translation.store'), [
                'province_id' => $province->id,
                'language_code' => $newLanguage->code,
                'name' => $name,
            ]);

            $response->assertCreated(); // Should handle Unicode gracefully
        }
    }

    public function test_handles_empty_and_null_optional_fields()
    {
        $province = Province::factory()->create();
        $translation = ProvinceTranslation::factory()->create(['province_id' => $province->id]);

        $testCases = [
            ['name' => null],
            ['name' => ''],
            ['name' => '   '], // Whitespace only
        ];

        foreach ($testCases as $data) {
            $updateData = array_merge([
                'province_id' => $translation->province_id,
                'language_code' => $translation->language_code,
            ], $data);

            $response = $this->putJson(route('province-translation.update', $translation), $updateData);

            // Should handle gracefully
            $this->assertContains($response->status(), [200, 422]);
        }
    }

    public function test_handles_very_long_translation_content()
    {
        $province = Province::factory()->create();
        $language = Language::factory()->create();

        $veryLongName = str_repeat('Very Long Province Name With Extended Geographic Description And Administrative Details ', 30);

        $response = $this->postJson(route('province-translation.store'), [
            'province_id' => $province->id,
            'language_code' => $language->code,
            'name' => $veryLongName,
        ]);

        // Should handle gracefully
        $this->assertContains($response->status(), [201, 422]);
    }

    public function test_handles_array_injection_attempts()
    {
        $province = Province::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('province-translation.store'), [
            'province_id' => ['array' => 'instead_of_uuid'],
            'language_code' => ['malicious' => 'array'],
            'name' => ['injection' => 'attempt'],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['province_id']);
    }

    public function test_pagination_with_many_translations()
    {
        $province = Province::factory()->create();
        ProvinceTranslation::factory()->count(40)->create(['province_id' => $province->id]);

        $testCases = [
            ['page' => 1, 'per_page' => 10],
            ['page' => 2, 'per_page' => 15],
            ['page' => 1, 'per_page' => 100], // Maximum
        ];

        foreach ($testCases as $params) {
            $response = $this->getJson(route('province-translation.index', $params));
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
            $response = $this->getJson(route('province-translation.index', $params));
            $response->assertUnprocessable();
        }
    }

    public function test_handles_special_characters_in_translation_content()
    {
        $province = Province::factory()->create();

        $specialCharNames = [
            'Province "Nord-Pas-de-Calais"',
            "Province 'Île-de-France'",
            'Province & Territory',
            'Province: Administrative Region',
            'Province (Historical)',
            'Province - Northern Region',
            'Province @ Location',
            'Province #1',
            'Province 50% Area',
            'Province $Economic Zone',
            'Province *Special Status',
            'Province +Metropolitan',
            'Province =Autonomous',
            'Province |Border Region',
        ];

        foreach ($specialCharNames as $index => $name) {
            $newLanguage = Language::factory()->create(['code' => sprintf('p%02d', $index)]);

            $response = $this->postJson(route('province-translation.store'), [
                'province_id' => $province->id,
                'language_code' => $newLanguage->code,
                'name' => $name,
            ]);

            // Should handle gracefully
            $this->assertContains($response->status(), [201, 422]);
        }
    }

    public function test_handles_province_translation_workflow()
    {
        $province = Province::factory()->create();
        $english = Language::factory()->create(['code' => 'eng']);
        $french = Language::factory()->create(['code' => 'fra']);
        $spanish = Language::factory()->create(['code' => 'spa']);

        // Create translations in different languages
        $languages = [$english, $french, $spanish];
        $names = ['Northern Province', 'Province du Nord', 'Provincia del Norte'];

        foreach ($languages as $index => $language) {
            $response = $this->postJson(route('province-translation.store'), [
                'province_id' => $province->id,
                'language_code' => $language->code,
                'name' => $names[$index],
            ]);

            $response->assertCreated();
        }

        // Verify all translations exist
        $indexResponse = $this->getJson(route('province-translation.index'));
        $indexResponse->assertOk();
        $indexResponse->assertJsonPath('meta.total', 3);
    }

    public function test_handles_geographic_province_name_variations()
    {
        $province = Province::factory()->create();

        $geographicNames = [
            'Northern Territory',
            'Southern Region',
            'Eastern Province',
            'Western District',
            'Central Valley',
            'Mountain Province',
            'Coastal Region',
            'Border Territory',
            'Metropolitan Area',
            'Rural District',
            'Island Province',
            'Desert Region',
            'Forest Territory',
            'Plains District',
            'Highland Province',
        ];

        foreach ($geographicNames as $index => $name) {
            $newLanguage = Language::factory()->create(['code' => sprintf('g%02d', $index)]);

            $response = $this->postJson(route('province-translation.store'), [
                'province_id' => $province->id,
                'language_code' => $newLanguage->code,
                'name' => $name,
            ]);

            $response->assertCreated(); // Should handle geographic name variations
        }
    }
}

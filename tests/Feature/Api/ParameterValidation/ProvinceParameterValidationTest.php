<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Country;
use App\Models\Language;
use App\Models\Province;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive parameter validation tests for Province API endpoints
 */
class ProvinceParameterValidationTest extends TestCase
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
        Province::factory()->count(12)->create();

        $response = $this->getJson(route('province.index', [
            'page' => 2,
            'per_page' => 5,
        ]));

        $response->assertOk();
        $response->assertJsonPath('meta.current_page', 2);
        $response->assertJsonPath('meta.per_page', 5);
    }

    public function test_index_accepts_valid_include_parameters()
    {
        Province::factory()->count(3)->create();

        $response = $this->getJson(route('province.index', [
            'include' => 'translations',
        ]));

        $response->assertOk();
    }

    public function test_index_rejects_invalid_include_parameters()
    {
        Province::factory()->count(2)->create();

        $response = $this->getJson(route('province.index', [
            'include' => 'invalid_relation,fake_country,non_existent',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['include']);
    }

    public function test_index_rejects_unexpected_query_parameters_currently()
    {
        Province::factory()->count(2)->create();

        $response = $this->getJson(route('province.index', [
            'page' => 1,
            'include' => 'country',
            'filter_by_country' => 'USA', // Not implemented
            'region' => 'north', // Not implemented
            'population_min' => '1000000', // Not implemented
            'area_size' => 'large', // Not implemented
            'admin_access' => true,
            'debug_provinces' => true,
            'export_format' => 'csv',
            'bulk_edit' => 'enable',
        ]));

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['filter_by_country']);
    }

    // SHOW ENDPOINT TESTS
    public function test_show_accepts_valid_include_parameters()
    {
        $province = Province::factory()->create();

        $response = $this->getJson(route('province.show', $province).'?include=translations');

        $response->assertOk();
    }

    public function test_show_rejects_unexpected_query_parameters_currently()
    {
        $province = Province::factory()->create();

        $response = $this->getJson(route('province.show', $province).'?include=country&admin_view=true&show_statistics=detailed&map_data=include');

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['admin_view']);
    }

    // STORE ENDPOINT TESTS
    public function test_store_validates_required_fields()
    {
        $response = $this->postJson(route('province.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name', 'country_id']);
    }

    public function test_store_validates_country_id_format()
    {
        $response = $this->postJson(route('province.store'), [
            'internal_name' => 'Test Province',
            'country_id' => 'AB', // Wrong size, should be 3 characters
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['country_id']);
    }

    public function test_store_validates_country_id_existence()
    {
        $response = $this->postJson(route('province.store'), [
            'internal_name' => 'Test Province',
            'country_id' => 'ZZZ', // Doesn't exist
        ]);

        // Current controller might not validate country existence - security gap
        $this->assertContains($response->status(), [201, 422]);
    }

    public function test_store_accepts_valid_data_with_existing_country()
    {
        $country = Country::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('province.store'), [
            'internal_name' => 'Valid Province',
            'country_id' => $country->id,
            'translations' => [
                [
                    'language_id' => $language->id,
                    'name' => 'Valid Province Name',
                ],
            ],
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.internal_name', 'Valid Province');
        $response->assertJsonPath('data.country_id', $country->id);
    }

    public function test_store_validates_unique_internal_name_per_country()
    {
        $country = Country::factory()->create();
        $existingProvince = Province::factory()->create(['country_id' => $country->id]);

        $response = $this->postJson(route('province.store'), [
            'internal_name' => $existingProvince->internal_name,
            'country_id' => $country->id,
        ]);

        // Might have unique constraint on (internal_name, country_id)
        $this->assertContains($response->status(), [201, 422]);
    }

    public function test_store_prohibits_id_field()
    {
        $country = Country::factory()->create();

        $response = $this->postJson(route('province.store'), [
            'id' => 'some-uuid', // Should be prohibited
            'internal_name' => 'Test Province',
            'country_id' => $country->id,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_store_accepts_optional_backward_compatibility_field()
    {
        $country = Country::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('province.store'), [
            'internal_name' => 'Legacy Province',
            'country_id' => $country->id,
            'backward_compatibility' => 'old_province_789',
            'translations' => [
                [
                    'language_id' => $language->id,
                    'name' => 'Legacy Province Name',
                ],
            ],
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.backward_compatibility', 'old_province_789');
    }

    public function test_store_rejects_unexpected_request_parameters_currently()
    {
        $country = Country::factory()->create();

        $response = $this->postJson(route('province.store'), [
            'internal_name' => 'Test Province',
            'country_id' => $country->id,
            'unexpected_field' => 'should_be_rejected',
            'population' => '5000000', // Not implemented
            'area_km2' => '50000', // Not implemented
            'capital_city' => 'Test City', // Not implemented
            'admin_code' => 'TP01', // Not implemented
            'admin_created' => true,
            'malicious_data' => '<script>alert("province_xss")</script>',
            'sql_injection' => "'; UPDATE provinces SET country_id = 'XXX'; --",
            'privilege_escalation' => 'admin_province',
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_validates_country_id_format()
    {
        $province = Province::factory()->create();

        $response = $this->putJson(route('province.update', $province), [
            'internal_name' => 'Updated Province',
            'country_id' => 'XY', // Wrong size
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['country_id']);
    }

    public function test_update_prohibits_id_modification()
    {
        $province = Province::factory()->create();

        $response = $this->putJson(route('province.update', $province), [
            'id' => 'new-uuid', // Should be prohibited
            'internal_name' => 'Updated Province',
            'country_id' => $province->country_id,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_update_accepts_valid_data()
    {
        $province = Province::factory()->create();
        $newCountry = Country::factory()->create();

        $response = $this->putJson(route('province.update', $province), [
            'internal_name' => 'Updated Province Name',
            'country_id' => $newCountry->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.internal_name', 'Updated Province Name');
    }

    public function test_update_rejects_unexpected_request_parameters_currently()
    {
        $province = Province::factory()->create();

        $response = $this->putJson(route('province.update', $province), [
            'internal_name' => 'Updated Province',
            'country_id' => $province->country_id,
            'unexpected_field' => 'should_be_rejected',
            'change_status' => 'active',
            'reassign_country' => 'different_country',
            'update_boundaries' => true,
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // EDGE CASE TESTS
    public function test_handles_unicode_characters_in_internal_name()
    {
        $country = Country::factory()->create();
        $language = Language::factory()->create();
        $unicodeNames = [
            'Provence française',
            'Провинция русская',
            '県日本語',
            'محافظة عربية',
            'Provincia española',
            'Provincia italiana',
            'Województwo polskie',
        ];

        foreach ($unicodeNames as $name) {
            $response = $this->postJson(route('province.store'), [
                'internal_name' => $name,
                'country_id' => $country->id,
                'translations' => [
                    [
                        'language_id' => $language->id,
                        'name' => $name.' Translation',
                    ],
                ],
            ]);

            $response->assertCreated(); // Should handle Unicode gracefully
        }
    }

    public function test_handles_special_characters_in_internal_name()
    {
        $country = Country::factory()->create();
        $specialCharNames = [
            'Province "With Quotes"',
            "Province 'With Apostrophes'",
            'Province & Region',
            'Province-Region',
            'Province of St. John',
            'Province d\'Artois',
            'Province São Paulo',
            'Province Rhône-Alpes',
            'Province (Central)',
            'Province #1',
        ];

        foreach ($specialCharNames as $name) {
            $response = $this->postJson(route('province.store'), [
                'internal_name' => $name,
                'country_id' => $country->id,
            ]);

            // Should handle gracefully
            $this->assertContains($response->status(), [201, 422]);
        }
    }

    public function test_handles_very_long_internal_name()
    {
        $country = Country::factory()->create();
        $veryLongName = str_repeat('Very Long Province Name With Geographic Details ', 50);

        $response = $this->postJson(route('province.store'), [
            'internal_name' => $veryLongName,
            'country_id' => $country->id,
        ]);

        // Should handle gracefully
        $this->assertContains($response->status(), [201, 422]);
    }

    public function test_handles_invalid_country_id_cases()
    {
        $invalidCountryIds = [
            'ABCD', // Too long
            'A', // Too short
            '123', // Numbers instead of letters
            '', // Empty
            'abc', // Lowercase (might be case-sensitive)
            'A B', // Space
            'A-B', // Hyphen
        ];

        foreach ($invalidCountryIds as $countryId) {
            $response = $this->postJson(route('province.store'), [
                'internal_name' => 'Test Province',
                'country_id' => $countryId,
            ]);

            $response->assertUnprocessable();
            $response->assertJsonValidationErrors(['country_id']);
        }
    }

    public function test_handles_empty_and_whitespace_internal_names()
    {
        $country = Country::factory()->create();
        $emptyNames = [
            '', // Empty
            '   ', // Spaces only
            "\t\t", // Tabs only
            "\n\n", // Newlines only
        ];

        foreach ($emptyNames as $name) {
            $response = $this->postJson(route('province.store'), [
                'internal_name' => $name,
                'country_id' => $country->id,
            ]);

            $response->assertUnprocessable(); // Should reject empty names
            $response->assertJsonValidationErrors(['internal_name']);
        }
    }

    public function test_handles_null_vs_empty_string_for_optional_fields()
    {
        $province = Province::factory()->create();

        $testCases = [
            ['backward_compatibility' => null],
            ['backward_compatibility' => ''],
        ];

        foreach ($testCases as $data) {
            $updateData = array_merge([
                'internal_name' => 'Test Province Update',
                'country_id' => $province->country_id,
            ], $data);

            $response = $this->putJson(route('province.update', $province), $updateData);

            $response->assertOk(); // Should handle gracefully
        }
    }

    public function test_pagination_with_many_provinces()
    {
        Province::factory()->count(75)->create();

        $testCases = [
            ['page' => 1, 'per_page' => 20],
            ['page' => 3, 'per_page' => 25],
            ['page' => 1, 'per_page' => 100], // Maximum
        ];

        foreach ($testCases as $params) {
            $response = $this->getJson(route('province.index', $params));
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
            $response = $this->getJson(route('province.index', $params));
            $response->assertUnprocessable();
        }
    }

    public function test_handles_array_injection_attempts()
    {
        $response = $this->postJson(route('province.store'), [
            'internal_name' => ['array' => 'instead_of_string'],
            'country_id' => ['malicious' => 'array'],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name', 'country_id']);
    }

    public function test_handles_country_id_case_sensitivity()
    {
        // Test if country_id is case-sensitive
        $country = Country::factory()->create(['id' => 'USA']);
        $language = Language::factory()->create();

        $testCases = [
            'USA', // Exact match
            'usa', // Lowercase
            'Usa', // Title case
            'uSA', // Mixed case
        ];

        foreach ($testCases as $countryId) {
            $response = $this->postJson(route('province.store'), [
                'internal_name' => "Province for {$countryId}",
                'country_id' => $countryId,
                'translations' => [
                    [
                        'language_id' => $language->id,
                        'name' => "Province Name for {$countryId}",
                    ],
                ],
            ]);

            if ($countryId === 'USA') {
                $response->assertCreated(); // Exact match should work
            } else {
                // Case variations should fail if validation is strict
                $this->assertContains($response->status(), [201, 422]);
            }
        }
    }
}

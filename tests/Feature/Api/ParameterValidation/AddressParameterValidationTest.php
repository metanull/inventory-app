<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Address;
use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive parameter validation tests for Address API endpoints
 */
class AddressParameterValidationTest extends TestCase
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
        Address::factory()->count(18)->create();

        $response = $this->getJson(route('address.index', [
            'page' => 3,
            'per_page' => 6,
        ]));

        $response->assertOk();
        $response->assertJsonPath('meta.current_page', 3);
        $response->assertJsonPath('meta.per_page', 6);
    }

    public function test_index_accepts_valid_include_parameters()
    {
        Address::factory()->count(3)->create();

        $response = $this->getJson(route('address.index', [
            'include' => 'translations',
        ]));

        $response->assertOk();
    }

    public function test_index_rejects_invalid_include_parameters()
    {
        Address::factory()->count(2)->create();

        $response = $this->getJson(route('address.index', [
            'include' => 'invalid_relation,fake_location,non_existent',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['include']);
    }

    public function test_index_rejects_unexpected_query_parameters()
    {
        // SECURITY TEST: Form Request should reject unexpected parameters
        Address::factory()->count(2)->create();

        $response = $this->getJson(route('address.index', [
            'page' => 1,
            'include' => 'translations',
            'filter_by_location' => 'uuid', // Not implemented
            'postal_code_range' => '10000-20000', // Not implemented
            'street_type' => 'avenue', // Not implemented
            'verified_only' => true, // Not implemented
            'admin_access' => true,
            'debug_addresses' => true,
            'export_format' => 'csv',
            'geocode_validation' => 'enable',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'filter_by_location',
                'postal_code_range',
                'street_type',
                'verified_only',
                'admin_access',
                'debug_addresses',
                'export_format',
                'geocode_validation',
            ],
        ]);
    }

    // SHOW ENDPOINT TESTS
    public function test_show_accepts_valid_include_parameters()
    {
        $address = Address::factory()->create();

        $response = $this->getJson(route('address.show', $address).'?include=translations');

        $response->assertOk();
    }

    public function test_show_rejects_unexpected_query_parameters()
    {
        // SECURITY TEST: Form Request should reject unexpected parameters
        $address = Address::factory()->create();

        $response = $this->getJson(route('address.show', $address).'?include=translations&admin_view=true&show_full_address=1&map_link=generate');

        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'admin_view',
                'show_full_address',
                'map_link',
            ],
        ]);
    }

    // STORE ENDPOINT TESTS
    public function test_store_validates_required_fields()
    {
        $response = $this->postJson(route('address.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name', 'country_id']);
    }

    public function test_store_validates_country_id_format()
    {
        $response = $this->postJson(route('address.store'), [
            'internal_name' => 'Test Address',
            'country_id' => 'invalid-format',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['country_id']);
    }

    public function test_store_validates_country_id_existence()
    {
        $response = $this->postJson(route('address.store'), [
            'internal_name' => 'Test Address',
            'country_id' => 'XXX', // Valid format but doesn't exist
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['country_id']);
    }

    public function test_store_accepts_valid_data_with_existing_country()
    {
        $country = Country::factory()->create();

        $response = $this->postJson(route('address.store'), [
            'internal_name' => 'Valid Address',
            'country_id' => $country->id,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.internal_name', 'Valid Address');
        $response->assertJsonPath('data.country_id', $country->id);
    }

    public function test_store_prohibits_id_field()
    {
        $country = Country::factory()->create();

        $response = $this->postJson(route('address.store'), [
            'id' => 'some-uuid', // Should be prohibited
            'internal_name' => 'Test Address',
            'country_id' => $country->id,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_store_accepts_optional_backward_compatibility_field()
    {
        $country = Country::factory()->create();

        $response = $this->postJson(route('address.store'), [
            'internal_name' => 'Legacy Address',
            'country_id' => $country->id,
            'backward_compatibility' => 'old_address_456',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.backward_compatibility', 'old_address_456');
    }

    public function test_store_rejects_unexpected_request_parameters()
    {
        // SECURITY TEST: Form Request should reject unexpected parameters
        $response = $this->postJson(route('address.store'), [
            'internal_name' => 'Test Address',
            'country_id' => 'usa',
            'translations' => [
                [
                    'language_id' => 'eng',
                    'address' => 'Main Street 123',
                ],
            ],
            'unexpected_field' => 'should_be_rejected',
            'street_number' => '123', // Not implemented
            'street_name' => 'Main St', // Not implemented
            'apartment' => 'Apt 4B', // Not implemented
            'building_name' => 'City Tower', // Not implemented
            'admin_created' => true,
            'malicious_address' => '<script>alert("address_xss")</script>',
            'sql_payload' => "'; UPDATE addresses SET country_id = NULL; --",
            'privilege_override' => 'admin_address',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'unexpected_field',
                'street_number',
                'street_name',
                'apartment',
                'building_name',
                'admin_created',
                'malicious_address',
                'sql_payload',
                'privilege_override',
            ],
        ]);
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_validates_country_id_uuid_format()
    {
        $address = Address::factory()->create();

        $response = $this->putJson(route('address.update', $address), [
            'internal_name' => 'Updated Address',
            'country_id' => 'invalid-format',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['country_id']);
    }

    public function test_update_prohibits_id_modification()
    {
        $address = Address::factory()->create();

        $response = $this->putJson(route('address.update', $address), [
            'id' => 'new-uuid', // Should be prohibited
            'internal_name' => 'Updated Address',
            'country_id' => $address->country_id,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_update_accepts_valid_data()
    {
        $address = Address::factory()->create();
        $newCountry = Country::factory()->create();

        $response = $this->putJson(route('address.update', $address), [
            'internal_name' => 'Updated Address Name',
            'country_id' => $newCountry->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.internal_name', 'Updated Address Name');
    }

    public function test_update_rejects_unexpected_request_parameters()
    {
        // SECURITY TEST: Form Request should reject unexpected parameters
        $address = Address::factory()->create();

        $response = $this->putJson(route('address.update', $address), [
            'internal_name' => 'Updated Address',
            'country_id' => $address->country_id,
            'translations' => [
                [
                    'language_id' => 'eng',
                    'address' => 'Updated Main Street 123',
                ],
            ],
            'unexpected_field' => 'should_be_rejected',
            'verify_address' => true,
            'update_coordinates' => 'auto',
            'change_primary' => true,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'unexpected_field',
                'verify_address',
                'update_coordinates',
                'change_primary',
            ],
        ]);
    }

    // EDGE CASE TESTS
    public function test_handles_unicode_characters_in_internal_name()
    {
        $country = Country::factory()->create();
        $unicodeNames = [
            'Adresse française',
            'Адрес русский',
            '住所日本語',
            'عنوان عربي',
            'Dirección española',
            'Indirizzo italiano',
            'Adres polski',
            'Διεύθυνση ελληνική',
        ];

        foreach ($unicodeNames as $name) {
            $response = $this->postJson(route('address.store'), [
                'internal_name' => $name,
                'country_id' => $country->id,
            ]);

            $response->assertCreated(); // Should handle Unicode gracefully
        }
    }

    public function test_handles_special_characters_in_internal_name()
    {
        $country = Country::factory()->create();
        $specialCharNames = [
            'Address "With Quotes"',
            "Address 'With Apostrophes'",
            'Address & Co.',
            'Address-Building',
            'Address of St. John',
            'Address d\'Artois',
            'Address São Paulo',
            'Address Côte-d\'Or',
            'Address (Building A)',
            'Address #123',
            'Address @ Plaza',
            'Address % Main',
            'Address / Complex',
        ];

        foreach ($specialCharNames as $name) {
            $response = $this->postJson(route('address.store'), [
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
        $veryLongName = str_repeat('Very Long Address Name With Building Details And Geographic References ', 25);

        $response = $this->postJson(route('address.store'), [
            'internal_name' => $veryLongName,
            'country_id' => $country->id,
        ]);

        // Should handle gracefully
        $this->assertContains($response->status(), [201, 422]);
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
            $response = $this->postJson(route('address.store'), [
                'internal_name' => $name,
                'country_id' => $country->id,
            ]);

            $response->assertUnprocessable(); // Should reject empty names
            $response->assertJsonValidationErrors(['internal_name']);
        }
    }

    public function test_handles_malformed_country_id_variations()
    {
        $malformedCountryIds = [
            '123', // Too short
            'ABCD', // Too long
            '12', // Too short
            '', // Empty
            'null', // String null
            'xx-', // Invalid format
        ];

        foreach ($malformedCountryIds as $countryId) {
            $response = $this->postJson(route('address.store'), [
                'internal_name' => 'Test Address',
                'country_id' => $countryId,
            ]);

            $response->assertUnprocessable(); // Should reject malformed country IDs
            $response->assertJsonValidationErrors(['country_id']);
        }
    }

    public function test_handles_null_vs_empty_string_for_optional_fields()
    {
        $address = Address::factory()->create();

        $testCases = [
            ['backward_compatibility' => null],
            ['backward_compatibility' => ''],
        ];

        foreach ($testCases as $data) {
            $updateData = array_merge([
                'internal_name' => 'Test Address Update',
                'country_id' => $address->country_id,
            ], $data);

            $response = $this->putJson(route('address.update', $address), $updateData);

            $response->assertOk(); // Should handle gracefully
        }
    }

    public function test_pagination_with_many_addresses()
    {
        Address::factory()->count(120)->create();

        $testCases = [
            ['page' => 1, 'per_page' => 25],
            ['page' => 3, 'per_page' => 40],
            ['page' => 1, 'per_page' => 100], // Maximum
        ];

        foreach ($testCases as $params) {
            $response = $this->getJson(route('address.index', $params));
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
            $response = $this->getJson(route('address.index', $params));
            $response->assertUnprocessable();
        }
    }

    public function test_handles_array_injection_attempts()
    {
        $response = $this->postJson(route('address.store'), [
            'internal_name' => ['array' => 'instead_of_string'],
            'country_id' => ['malicious' => 'array'],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name', 'country_id']);
    }

    public function test_handles_address_format_variations()
    {
        $country = Country::factory()->create();
        $addressFormats = [
            '123 Main Street',
            '456 Oak Ave., Suite 789',
            'Building A, Floor 12, Room 345',
            'PO Box 12345',
            'Rural Route 1, Box 67',
            'General Delivery',
            '789 First St. NE',
            'Apartment 4B, 321 Second Avenue',
        ];

        foreach ($addressFormats as $addressFormat) {
            $response = $this->postJson(route('address.store'), [
                'internal_name' => $addressFormat,
                'country_id' => $country->id,
            ]);

            $response->assertCreated(); // Should handle various address formats
        }
    }
}

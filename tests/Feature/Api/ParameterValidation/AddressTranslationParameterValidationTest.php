<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Address;
use App\Models\AddressTranslation;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive parameter validation tests for AddressTranslation API endpoints
 */
class AddressTranslationParameterValidationTest extends TestCase
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
        $address = Address::factory()->create();
        AddressTranslation::factory()->count(20)->create(['address_id' => $address->id]);

        $response = $this->getJson(route('address-translation.index', [
            'page' => 3,
            'per_page' => 7,
        ]));

        $response->assertOk();
        $response->assertJsonPath('meta.current_page', 3);
        $response->assertJsonPath('meta.per_page', 7);
    }

    public function test_index_accepts_valid_include_parameters()
    {
        $address = Address::factory()->create();
        AddressTranslation::factory()->count(3)->create(['address_id' => $address->id]);

        $response = $this->getJson(route('address-translation.index', [
            'include' => 'address,language',
        ]));

        $response->assertOk();
    }

    public function test_index_rejects_invalid_include_parameters()
    {
        $address = Address::factory()->create();
        AddressTranslation::factory()->count(2)->create(['address_id' => $address->id]);

        $response = $this->getJson(route('address-translation.index', [
            'include' => 'invalid_relation,fake_address,non_existent',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['include']);
    }

    public function test_index_rejects_unexpected_query_parameters_currently()
    {
        $address = Address::factory()->create();
        AddressTranslation::factory()->count(2)->create(['address_id' => $address->id]);

        $response = $this->getJson(route('address-translation.index', [
            'page' => 1,
            'include' => 'address',
            'filter_by_location' => 'downtown', // Not implemented
            'address_type' => 'residential', // Not implemented
            'postal_district' => '12345', // Not implemented
            'admin_access' => true,
            'debug_translations' => true,
            'export_format' => 'csv',
            'bulk_operation' => 'standardize_all',
        ]));

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['filter_by_location']);
    }

    // SHOW ENDPOINT TESTS
    public function test_show_accepts_valid_include_parameters()
    {
        $address = Address::factory()->create();
        $translation = AddressTranslation::factory()->create(['address_id' => $address->id]);

        $response = $this->getJson(route('address-translation.show', $translation).'?include=address,language');

        $response->assertOk();
    }

    public function test_show_rejects_unexpected_query_parameters_currently()
    {
        $address = Address::factory()->create();
        $translation = AddressTranslation::factory()->create(['address_id' => $address->id]);

        $response = $this->getJson(route('address-translation.show', $translation).'?include=address&show_map=true&geocoding_details=full');

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['show_map']);
    }

    // STORE ENDPOINT TESTS
    public function test_store_validates_required_fields()
    {
        $response = $this->postJson(route('address-translation.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['address_id', 'language_code']);
    }

    public function test_store_validates_address_id_exists()
    {
        $language = Language::factory()->create();

        $response = $this->postJson(route('address-translation.store'), [
            'address_id' => 'non-existent-uuid',
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['address_id']);
    }

    public function test_store_validates_language_code_exists()
    {
        $address = Address::factory()->create();

        $response = $this->postJson(route('address-translation.store'), [
            'address_id' => $address->id,
            'language_code' => 'xyz', // Invalid language code
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_code']);
    }

    public function test_store_validates_unique_combination()
    {
        $address = Address::factory()->create();
        $language = Language::factory()->create();
        AddressTranslation::factory()->create([
            'address_id' => $address->id,
            'language_code' => $language->code,
        ]);

        $response = $this->postJson(route('address-translation.store'), [
            'address_id' => $address->id,
            'language_code' => $language->code, // Duplicate combination
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['address_id']);
    }

    public function test_store_validates_address_id_uuid_format()
    {
        $language = Language::factory()->create();

        $response = $this->postJson(route('address-translation.store'), [
            'address_id' => 'not-a-uuid',
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['address_id']);
    }

    public function test_store_validates_language_code_format()
    {
        $address = Address::factory()->create();

        $response = $this->postJson(route('address-translation.store'), [
            'address_id' => $address->id,
            'language_code' => 'toolong', // Should be 3 characters
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_code']);
    }

    public function test_store_prohibits_id_field()
    {
        $address = Address::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('address-translation.store'), [
            'id' => 'some-uuid', // Should be prohibited
            'address_id' => $address->id,
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_store_accepts_valid_data()
    {
        $address = Address::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('address-translation.store'), [
            'address_id' => $address->id,
            'language_code' => $language->code,
            'street_address' => 'Translated Street Address',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.address_id', $address->id);
        $response->assertJsonPath('data.language_code', $language->code);
    }

    public function test_store_rejects_unexpected_request_parameters_currently()
    {
        $address = Address::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('address-translation.store'), [
            'address_id' => $address->id,
            'language_code' => $language->code,
            'street_address' => 'Test Address Translation',
            'unexpected_field' => 'should_be_rejected',
            'building_name' => 'Main Building', // Not implemented
            'floor_number' => '3rd Floor', // Not implemented
            'apartment_number' => 'Apt 123', // Not implemented
            'local_district' => 'Downtown', // Not implemented
            'admin_created' => true,
            'malicious_script' => '<iframe src="data:text/html;base64,PHNjcmlwdD5hbGVydCgiWFNTIik8L3NjcmlwdD4="></iframe>',
            'sql_injection' => "'; DROP TABLE address_translations; --",
            'privilege_escalation' => 'address_admin',
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_validates_unique_combination_on_change()
    {
        $address1 = Address::factory()->create();
        $address2 = Address::factory()->create();
        $language = Language::factory()->create();

        $translation1 = AddressTranslation::factory()->create([
            'address_id' => $address1->id,
            'language_code' => $language->code,
        ]);

        AddressTranslation::factory()->create([
            'address_id' => $address2->id,
            'language_code' => $language->code,
        ]);

        $response = $this->putJson(route('address-translation.update', $translation1), [
            'address_id' => $address2->id, // Would create duplicate
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['address_id']);
    }

    public function test_update_prohibits_id_modification()
    {
        $address = Address::factory()->create();
        $translation = AddressTranslation::factory()->create(['address_id' => $address->id]);

        $response = $this->putJson(route('address-translation.update', $translation), [
            'id' => 'new-uuid', // Should be prohibited
            'address_id' => $address->id,
            'language_code' => $translation->language_code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_update_accepts_same_combination()
    {
        $address = Address::factory()->create();
        $translation = AddressTranslation::factory()->create(['address_id' => $address->id]);

        $response = $this->putJson(route('address-translation.update', $translation), [
            'address_id' => $translation->address_id, // Same combination
            'language_code' => $translation->language_code,
            'street_address' => 'Updated Street Address',
        ]);

        $response->assertOk();
    }

    public function test_update_rejects_unexpected_request_parameters_currently()
    {
        $address = Address::factory()->create();
        $translation = AddressTranslation::factory()->create(['address_id' => $address->id]);

        $response = $this->putJson(route('address-translation.update', $translation), [
            'address_id' => $translation->address_id,
            'language_code' => $translation->language_code,
            'street_address' => 'Updated Address Translation',
            'unexpected_field' => 'should_be_rejected',
            'change_status' => 'verified',
            'update_geocoding' => 'precise',
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // EDGE CASE TESTS
    public function test_handles_unicode_characters_in_translation_fields()
    {
        $address = Address::factory()->create();

        $unicodeAddresses = [
            'Rue française 123',
            'Улица русская 456',
            '通り日本語 789',
            'شارع عربي 101',
            'Calle española 202',
            'Via italiana 303',
            'Ulica polska 404',
            'Οδός ελληνική 505',
            'Gade dansk 606',
            'Utca magyar 707',
        ];

        foreach ($unicodeAddresses as $index => $streetAddress) {
            $newLanguage = Language::factory()->create(['code' => sprintf('%03d', $index)]);

            $response = $this->postJson(route('address-translation.store'), [
                'address_id' => $address->id,
                'language_code' => $newLanguage->code,
                'street_address' => $streetAddress,
            ]);

            $response->assertCreated(); // Should handle Unicode gracefully
        }
    }

    public function test_handles_empty_and_null_optional_fields()
    {
        $address = Address::factory()->create();
        $translation = AddressTranslation::factory()->create(['address_id' => $address->id]);

        $testCases = [
            ['street_address' => null],
            ['street_address' => ''],
            ['street_address' => '   '], // Whitespace only
        ];

        foreach ($testCases as $data) {
            $updateData = array_merge([
                'address_id' => $translation->address_id,
                'language_code' => $translation->language_code,
            ], $data);

            $response = $this->putJson(route('address-translation.update', $translation), $updateData);

            // Should handle gracefully
            $this->assertContains($response->status(), [200, 422]);
        }
    }

    public function test_handles_very_long_translation_content()
    {
        $address = Address::factory()->create();
        $language = Language::factory()->create();

        $veryLongAddress = str_repeat('Very Long Street Address With Extended Building Description And Detailed Location Information ', 20);

        $response = $this->postJson(route('address-translation.store'), [
            'address_id' => $address->id,
            'language_code' => $language->code,
            'street_address' => $veryLongAddress,
        ]);

        // Should handle gracefully
        $this->assertContains($response->status(), [201, 422]);
    }

    public function test_handles_array_injection_attempts()
    {
        $address = Address::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('address-translation.store'), [
            'address_id' => ['array' => 'instead_of_uuid'],
            'language_code' => ['malicious' => 'array'],
            'street_address' => ['injection' => 'attempt'],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['address_id']);
    }

    public function test_pagination_with_many_translations()
    {
        $address = Address::factory()->create();
        AddressTranslation::factory()->count(45)->create(['address_id' => $address->id]);

        $testCases = [
            ['page' => 1, 'per_page' => 15],
            ['page' => 2, 'per_page' => 18],
            ['page' => 1, 'per_page' => 100], // Maximum
        ];

        foreach ($testCases as $params) {
            $response = $this->getJson(route('address-translation.index', $params));
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
            $response = $this->getJson(route('address-translation.index', $params));
            $response->assertUnprocessable();
        }
    }

    public function test_handles_special_characters_in_translation_content()
    {
        $address = Address::factory()->create();

        $specialCharAddresses = [
            'Address "Main Street" #123',
            "Address 'Oak Avenue' Apt B",
            'Address & Shopping Center',
            'Address: Building Complex',
            'Address (Suite 456)',
            'Address - North Wing',
            'Address @ Plaza',
            'Address #789',
            'Address 50% Complete',
            'Address $Premium Location',
            'Address *Historic Building',
            'Address +Extended Area',
            'Address =Business District',
            'Address |Border Location',
        ];

        foreach ($specialCharAddresses as $index => $streetAddress) {
            $newLanguage = Language::factory()->create(['code' => sprintf('a%02d', $index)]);

            $response = $this->postJson(route('address-translation.store'), [
                'address_id' => $address->id,
                'language_code' => $newLanguage->code,
                'street_address' => $streetAddress,
            ]);

            // Should handle gracefully
            $this->assertContains($response->status(), [201, 422]);
        }
    }

    public function test_handles_address_translation_workflow()
    {
        $address = Address::factory()->create();
        $english = Language::factory()->create(['code' => 'eng']);
        $french = Language::factory()->create(['code' => 'fra']);
        $spanish = Language::factory()->create(['code' => 'spa']);

        // Create translations in different languages
        $languages = [$english, $french, $spanish];
        $addresses = ['123 Main Street', '123 Rue Principale', '123 Calle Principal'];

        foreach ($languages as $index => $language) {
            $response = $this->postJson(route('address-translation.store'), [
                'address_id' => $address->id,
                'language_code' => $language->code,
                'street_address' => $addresses[$index],
            ]);

            $response->assertCreated();
        }

        // Verify all translations exist
        $indexResponse = $this->getJson(route('address-translation.index'));
        $indexResponse->assertOk();
        $indexResponse->assertJsonPath('meta.total', 3);
    }

    public function test_handles_international_address_format_variations()
    {
        $address = Address::factory()->create();

        $internationalAddresses = [
            '123 Main Street, Apt 4B',
            '456 Oak Avenue, Suite 200',
            '789 Elm Street, Unit 5',
            '101 Pine Road, Building A',
            '202 Maple Drive, Floor 3',
            '303 Cedar Lane, Room 12',
            '404 Birch Boulevard, Office 7',
            '505 Spruce Street, Loft 2',
            '606 Willow Way, Penthouse',
            '707 Aspen Avenue, Basement',
            '808 Poplar Place, Garage',
            '909 Hickory Hill, Studio',
            '1010 Walnut Way, Townhouse',
            '1111 Chestnut Circle, Duplex',
            '1212 Sycamore Square, Condo',
        ];

        foreach ($internationalAddresses as $index => $streetAddress) {
            $newLanguage = Language::factory()->create(['code' => sprintf('i%02d', $index)]);

            $response = $this->postJson(route('address-translation.store'), [
                'address_id' => $address->id,
                'language_code' => $newLanguage->code,
                'street_address' => $streetAddress,
            ]);

            $response->assertCreated(); // Should handle international address formats
        }
    }

    public function test_handles_postal_address_component_variations()
    {
        $address = Address::factory()->create();

        $postalAddressComponents = [
            'P.O. Box 12345',
            'General Delivery',
            'Rural Route 3',
            'Highway Contract 456',
            'Private Bag 789',
            'GPO Box 101',
            'Locked Bag 202',
            'Community Mail Bag',
            'Station A',
            'Postal Station B',
            'RPO West',
            'Postal Outlet',
            'Sub Post Office',
            'Delivery Centre',
            'Mail Processing Plant',
        ];

        foreach ($postalAddressComponents as $index => $streetAddress) {
            $newLanguage = Language::factory()->create(['code' => sprintf('s%02d', $index)]);

            $response = $this->postJson(route('address-translation.store'), [
                'address_id' => $address->id,
                'language_code' => $newLanguage->code,
                'street_address' => $streetAddress,
            ]);

            $response->assertCreated(); // Should handle postal address components
        }
    }
}

<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Address;
use App\Models\AddressTranslation;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Clean parameter validation tests for AddressTranslation API endpoints
 * Tests ONLY what Form Requests actually validate - no made-up functionality
 */
class CleanAddressTranslationParameterValidationTest extends TestCase
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
    public function test_index_validates_page_parameter_type()
    {
        $response = $this->getJson(route('address-translation.index', [
            'page' => 'not_a_number',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['page']);
    }

    public function test_index_validates_per_page_parameter_size()
    {
        $response = $this->getJson(route('address-translation.index', [
            'per_page' => 101, // Must be max:100
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['per_page']);
    }

    // STORE ENDPOINT TESTS
    public function test_store_handles_empty_payload()
    {
        $response = $this->postJson(route('address-translation.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'address_id',
            'language_id',
            'address',
        ]);
    }

    public function test_store_validates_address_id_type()
    {
        $response = $this->postJson(route('address-translation.store'), [
            'address_id' => 'not_a_uuid',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['address_id']);
    }

    public function test_store_validates_address_id_exists()
    {
        $response = $this->postJson(route('address-translation.store'), [
            'address_id' => '12345678-1234-1234-1234-123456789012',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['address_id']);
    }

    public function test_store_validates_language_id_type()
    {
        $address = Address::factory()->create();

        $response = $this->postJson(route('address-translation.store'), [
            'address_id' => $address->id,
            'language_id' => 123, // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_validates_language_id_size()
    {
        $address = Address::factory()->create();

        $response = $this->postJson(route('address-translation.store'), [
            'address_id' => $address->id,
            'language_id' => 'toolong', // Should be exactly 3 characters
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_validates_language_id_exists()
    {
        $address = Address::factory()->create();

        $response = $this->postJson(route('address-translation.store'), [
            'address_id' => $address->id,
            'language_id' => 'XYZ', // Non-existent language
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_validates_address_type()
    {
        $address = Address::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('address-translation.store'), [
            'address_id' => $address->id,
            'language_id' => $language->id,
            'address' => ['array', 'not', 'string'], // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['address']);
    }

    public function test_store_validates_description_type()
    {
        $address = Address::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('address-translation.store'), [
            'address_id' => $address->id,
            'language_id' => $language->id,
            'address' => 'Valid address',
            'description' => ['array', 'not', 'string'], // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['description']);
    }

    public function test_store_validates_backward_compatibility_type()
    {
        $address = Address::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('address-translation.store'), [
            'address_id' => $address->id,
            'language_id' => $language->id,
            'address' => 'Valid address',
            'backward_compatibility' => ['array', 'not', 'string'], // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['backward_compatibility']);
    }

    public function test_store_validates_backward_compatibility_max_length()
    {
        $address = Address::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('address-translation.store'), [
            'address_id' => $address->id,
            'language_id' => $language->id,
            'address' => 'Valid address',
            'backward_compatibility' => str_repeat('a', 256), // Exceeds max:255
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['backward_compatibility']);
    }

    public function test_store_accepts_valid_data()
    {
        $address = Address::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('address-translation.store'), [
            'address_id' => $address->id,
            'language_id' => $language->id,
            'address' => 'Valid Address Translation',
            'description' => 'Valid description',
            'backward_compatibility' => 'old_id_123',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.address', 'Valid Address Translation');
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_handles_empty_payload()
    {
        $translation = AddressTranslation::factory()->create();

        $response = $this->putJson(route('address-translation.update', $translation), []);

        $response->assertOk(); // Update allows partial updates
    }

    public function test_update_validates_wrong_parameter_types()
    {
        $translation = AddressTranslation::factory()->create();

        $response = $this->putJson(route('address-translation.update', $translation), [
            'address_id' => 'not_a_uuid',
            'language_id' => 123,
            'address' => ['array', 'not', 'string'],
            'description' => ['array', 'not', 'string'],
            'backward_compatibility' => ['array', 'not', 'string'],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'address_id',
            'language_id',
            'address',
            'description',
            'backward_compatibility',
        ]);
    }

    public function test_update_accepts_valid_data()
    {
        $translation = AddressTranslation::factory()->create();
        $address = Address::factory()->create();
        $language = Language::factory()->create();

        $response = $this->putJson(route('address-translation.update', $translation), [
            'address_id' => $address->id,
            'language_id' => $language->id,
            'address' => 'Updated Address Translation',
            'description' => 'Updated description',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.address', 'Updated Address Translation');
    }
}

<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Language;
use App\Models\Location;
use App\Models\LocationTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Clean parameter validation tests for LocationTranslation API endpoints
 * Tests ONLY what Form Requests actually validate - no made-up functionality
 */
class CleanLocationTranslationParameterValidationTest extends TestCase
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
        $response = $this->getJson(route('location-translation.index', [
            'page' => 'not_a_number',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['page']);
    }

    public function test_index_validates_per_page_parameter_size()
    {
        $response = $this->getJson(route('location-translation.index', [
            'per_page' => 101, // Must be max:100
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['per_page']);
    }

    // STORE ENDPOINT TESTS
    public function test_store_handles_empty_payload()
    {
        $response = $this->postJson(route('location-translation.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'location_id',
            'language_id',
            'name',
        ]);
    }

    public function test_store_validates_location_id_type()
    {
        $response = $this->postJson(route('location-translation.store'), [
            'location_id' => 'not_a_uuid',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['location_id']);
    }

    public function test_store_validates_location_id_exists()
    {
        $response = $this->postJson(route('location-translation.store'), [
            'location_id' => '12345678-1234-1234-1234-123456789012',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['location_id']);
    }

    public function test_store_validates_language_id_type()
    {
        $location = Location::factory()->create();

        $response = $this->postJson(route('location-translation.store'), [
            'location_id' => $location->id,
            'language_id' => 123, // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_validates_language_id_size()
    {
        $location = Location::factory()->create();

        $response = $this->postJson(route('location-translation.store'), [
            'location_id' => $location->id,
            'language_id' => 'toolong', // Should be exactly 3 characters
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_validates_language_id_exists()
    {
        $location = Location::factory()->create();

        $response = $this->postJson(route('location-translation.store'), [
            'location_id' => $location->id,
            'language_id' => 'XYZ', // Non-existent language
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_validates_name_type()
    {
        $location = Location::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('location-translation.store'), [
            'location_id' => $location->id,
            'language_id' => $language->id,
            'name' => ['array', 'not', 'string'], // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_store_validates_name_size()
    {
        $location = Location::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('location-translation.store'), [
            'location_id' => $location->id,
            'language_id' => $language->id,
            'name' => str_repeat('a', 256), // Exceeds max:255
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_store_validates_description_type()
    {
        $location = Location::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('location-translation.store'), [
            'location_id' => $location->id,
            'language_id' => $language->id,
            'name' => 'Test Name',
            'description' => 12345, // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['description']);
    }

    public function test_store_accepts_valid_data()
    {
        $location = Location::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('location-translation.store'), [
            'location_id' => $location->id,
            'language_id' => $language->id,
            'name' => 'Test Location Name',
            'description' => 'Test location description',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.location_id', $location->id);
        $response->assertJsonPath('data.language_id', $language->id);
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_handles_empty_payload()
    {
        $translation = LocationTranslation::factory()->create();

        $response = $this->putJson(route('location-translation.update', $translation), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'location_id',
            'language_id',
            'name',
        ]);
    }

    public function test_update_validates_wrong_parameter_types()
    {
        $translation = LocationTranslation::factory()->create();

        $response = $this->putJson(route('location-translation.update', $translation), [
            'location_id' => 'not_uuid',
            'language_id' => 123, // Should be string
            'name' => ['array'], // Should be string
            'description' => 456, // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'location_id',
            'language_id',
            'name',
        ]);
    }

    public function test_update_accepts_valid_data()
    {
        $translation = LocationTranslation::factory()->create();

        $response = $this->putJson(route('location-translation.update', $translation), [
            'location_id' => $translation->location_id,
            'language_id' => $translation->language_id,
            'name' => 'Updated Location Name',
            'description' => 'Updated location description',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.name', 'Updated Location Name');
        $response->assertJsonPath('data.description', 'Updated location description');
    }
}

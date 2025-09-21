<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Language;
use App\Models\Province;
use App\Models\ProvinceTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Clean parameter validation tests for ProvinceTranslation API endpoints
 * Tests ONLY what Form Requests actually validate - no made-up functionality
 */
class CleanProvinceTranslationParameterValidationTest extends TestCase
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
        $response = $this->getJson(route('province-translation.index', [
            'page' => 'not_a_number',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['page']);
    }

    public function test_index_validates_per_page_parameter_size()
    {
        $response = $this->getJson(route('province-translation.index', [
            'per_page' => 101, // Must be max:100
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['per_page']);
    }

    // STORE ENDPOINT TESTS
    public function test_store_handles_empty_payload()
    {
        $response = $this->postJson(route('province-translation.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'province_id',
            'language_id',
            'name',
        ]);
    }

    public function test_store_validates_province_id_type()
    {
        $response = $this->postJson(route('province-translation.store'), [
            'province_id' => 'not_a_uuid',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['province_id']);
    }

    public function test_store_validates_province_id_exists()
    {
        $response = $this->postJson(route('province-translation.store'), [
            'province_id' => '12345678-1234-1234-1234-123456789012',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['province_id']);
    }

    public function test_store_validates_language_id_type()
    {
        $province = Province::factory()->create();

        $response = $this->postJson(route('province-translation.store'), [
            'province_id' => $province->id,
            'language_id' => 123, // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_validates_language_id_size()
    {
        $province = Province::factory()->create();

        $response = $this->postJson(route('province-translation.store'), [
            'province_id' => $province->id,
            'language_id' => 'toolong', // Should be exactly 3 characters
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_validates_language_id_exists()
    {
        $province = Province::factory()->create();

        $response = $this->postJson(route('province-translation.store'), [
            'province_id' => $province->id,
            'language_id' => 'XYZ', // Non-existent language
            'name' => 'Test name',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_validates_name_type()
    {
        $province = Province::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('province-translation.store'), [
            'province_id' => $province->id,
            'language_id' => $language->id,
            'name' => ['array', 'not', 'string'], // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_store_validates_name_size()
    {
        $province = Province::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('province-translation.store'), [
            'province_id' => $province->id,
            'language_id' => $language->id,
            'name' => str_repeat('a', 256), // Exceeds max:255
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_store_validates_description_type()
    {
        $province = Province::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('province-translation.store'), [
            'province_id' => $province->id,
            'language_id' => $language->id,
            'name' => 'Test Name',
            'description' => 12345, // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['description']);
    }

    public function test_store_accepts_valid_data()
    {
        $province = Province::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('province-translation.store'), [
            'province_id' => $province->id,
            'language_id' => $language->id,
            'name' => 'Test Province Name',
            'description' => 'Test province description',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.province_id', $province->id);
        $response->assertJsonPath('data.language_id', $language->id);
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_handles_empty_payload()
    {
        $translation = ProvinceTranslation::factory()->create();

        $response = $this->putJson(route('province-translation.update', $translation), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'province_id',
            'language_id',
            'name',
        ]);
    }

    public function test_update_validates_wrong_parameter_types()
    {
        $translation = ProvinceTranslation::factory()->create();

        $response = $this->putJson(route('province-translation.update', $translation), [
            'province_id' => 'not_uuid',
            'language_id' => 123, // Should be string
            'name' => ['array'], // Should be string
            'description' => 456, // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'province_id',
            'language_id',
            'name',
        ]);
    }

    public function test_update_accepts_valid_data()
    {
        $translation = ProvinceTranslation::factory()->create();

        $response = $this->putJson(route('province-translation.update', $translation), [
            'province_id' => $translation->province_id,
            'language_id' => $translation->language_id,
            'name' => 'Updated Province Name',
            'description' => 'Updated province description',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.name', 'Updated Province Name');
        $response->assertJsonPath('data.description', 'Updated province description');
    }
}

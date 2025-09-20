<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive parameter validation tests for Language API endpoints
 */
class LanguageParameterValidationTest extends TestCase
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
        Language::factory()->count(5)->create();

        $response = $this->getJson(route('language.index', [
            'page' => 1,
            'per_page' => 3,
        ]));

        $response->assertOk();
        $response->assertJsonPath('meta.per_page', 3);
    }

    public function test_index_rejects_unexpected_query_parameters_securely()
    {
        // SECURITY TEST: Form Request should reject unexpected parameters
        Language::factory()->count(2)->create();

        $response = $this->getJson(route('language.index', [
            'page' => 1,
            'unexpected_param' => 'test',
            'malicious_param' => 'injection',
        ]));

        $response->assertStatus(422); // Form Request now correctly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_param', 'malicious_param']);
    }

    public function test_index_validates_pagination_bounds()
    {
        Language::factory()->count(2)->create();

        $response = $this->getJson(route('language.index', [
            'per_page' => 101,  // Above limit
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['per_page']);
    }

    // SHOW ENDPOINT TESTS
    public function test_show_rejects_unexpected_query_parameters_securely()
    {
        $language = Language::factory()->create();

        $response = $this->getJson(route('language.show', $language).'?unexpected=value');

        $response->assertStatus(422); // Form Request now correctly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected']);
    }

    // STORE ENDPOINT TESTS
    public function test_store_validates_required_fields()
    {
        $response = $this->postJson(route('language.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id', 'internal_name']);
    }

    public function test_store_validates_field_types_and_constraints()
    {
        $response = $this->postJson(route('language.store'), [
            'id' => 'TOOLONG', // Should be exactly 3 characters
            'internal_name' => 123, // Should be string
            'backward_compatibility' => 'TOO', // Should be max 2 characters
            'is_default' => 'invalid', // Should be boolean, but it's prohibited anyway
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id', 'internal_name', 'backward_compatibility', 'is_default']);
    }

    public function test_store_prohibits_is_default_field()
    {
        $data = Language::factory()->make()->toArray();
        $data['is_default'] = true; // This should be prohibited

        $response = $this->postJson(route('language.store'), $data);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['is_default']);
    }

    public function test_store_validates_unique_constraint()
    {
        $existingLanguage = Language::factory()->create();

        $response = $this->postJson(route('language.store'), [
            'id' => $existingLanguage->id,
            'internal_name' => 'Different Name',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_store_rejects_unexpected_request_parameters_securely()
    {
        // SECURITY TEST: Form Request should reject unexpected parameters
        $data = Language::factory()->make()->toArray();
        $data['unexpected_field'] = 'should_be_rejected';
        $data['malicious_injection'] = '<script>alert("xss")</script>';

        $response = $this->postJson(route('language.store'), $data);

        $response->assertStatus(422); // Form Request now correctly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field', 'malicious_injection']);
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_validates_field_types()
    {
        $language = Language::factory()->create();

        $response = $this->putJson(route('language.update', $language), [
            'internal_name' => 123, // Should be string
            'backward_compatibility' => 'TOO', // Should be max 2 characters
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name', 'backward_compatibility']);
    }

    public function test_update_prohibits_id_and_is_default_modification()
    {
        $language = Language::factory()->create();

        $response = $this->putJson(route('language.update', $language), [
            'id' => 'NEW', // Should be prohibited
            'internal_name' => 'Valid Name',
            'is_default' => true, // Should be prohibited
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id', 'is_default']);
    }

    public function test_update_rejects_unexpected_request_parameters_securely()
    {
        // SECURITY TEST: Form Request should reject unexpected parameters
        $language = Language::factory()->create();

        $response = $this->putJson(route('language.update', $language), [
            'internal_name' => 'Updated Name',
            'unexpected_field' => 'should_be_rejected',
        ]);

        $response->assertStatus(422); // Form Request now correctly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // SET DEFAULT ENDPOINT TESTS (Special endpoint for Language)
    public function test_set_default_validates_required_field()
    {
        $language = Language::factory()->create();

        $response = $this->patchJson(route('language.setDefault', $language), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['is_default']);
    }

    public function test_set_default_validates_field_type()
    {
        $language = Language::factory()->create();

        $response = $this->patchJson(route('language.setDefault', $language), [
            'is_default' => 'invalid', // Should be boolean
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['is_default']);
    }

    public function test_set_default_rejects_unexpected_parameters_currently()
    {
        $language = Language::factory()->create();

        $response = $this->patchJson(route('language.setDefault', $language), [
            'is_default' => true,
            'unexpected_field' => 'should_be_rejected',
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // EDGE CASE TESTS
    public function test_handles_excessively_long_parameters_without_crashing()
    {
        $veryLongString = str_repeat('a', 10000);

        $response = $this->postJson(route('language.store'), [
            'id' => 'ENG',
            'internal_name' => $veryLongString,
            'unexpected_huge_field' => $veryLongString,
        ]);

        $response->assertUnprocessable(); // Should handle gracefully
    }

    public function test_handles_unicode_and_special_characters()
    {
        $data = Language::factory()->make([
            'internal_name' => 'Español 中文 🌍',
            'backward_compatibility' => 'ñ1',
        ])->toArray();

        $response = $this->postJson(route('language.store'), $data);

        $this->assertContains($response->status(), [201, 422]);
    }
}

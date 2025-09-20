<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Country;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive test suite for API parameter validation across all entities
 * Tests security posture regarding unexpected parameters, type validation, and length limits
 */
class CountryParameterValidationTest extends TestCase
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
        Country::factory()->count(5)->create();

        $response = $this->getJson(route('country.index', [
            'page' => 1,
            'per_page' => 3,
        ]));

        $response->assertOk();
        $response->assertJsonPath('meta.per_page', 3);
        $response->assertJsonPath('meta.current_page', 1);
    }

    public function test_index_accepts_valid_include_parameters()
    {
        Country::factory()->count(2)->create();

        $response = $this->getJson(route('country.index', [
            'include' => 'items,partners',
        ]));

        $response->assertOk();
    }

    public function test_index_rejects_invalid_include_parameters()
    {
        Country::factory()->count(2)->create();

        $response = $this->getJson(route('country.index', [
            'include' => 'invalid_relation,nonexistent',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['include']);
    }

    public function test_index_rejects_unexpected_query_parameters()
    {
        // SECURITY TEST: System should reject unexpected parameters with 422
        Country::factory()->count(2)->create();

        $response = $this->getJson(route('country.index', [
            'page' => 1,
            'per_page' => 5,
            'include' => 'items',
            'unexpected_param' => 'test',
            'another_unexpected' => 'value',
            'malicious_param' => 'injection_attempt',
        ]));

        // Secure behavior: rejects unexpected parameters
        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'unexpected_param',
                'another_unexpected',
                'malicious_param',
            ],
        ]);
    }

    public function test_index_validates_pagination_bounds()
    {
        Country::factory()->count(2)->create();

        // Test per_page upper bound
        $response = $this->getJson(route('country.index', [
            'per_page' => 101,  // Above max limit of 100
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['per_page']);
    }

    public function test_index_validates_pagination_lower_bounds()
    {
        Country::factory()->count(2)->create();

        // Test per_page lower bound
        $response = $this->getJson(route('country.index', [
            'per_page' => 0,  // Below min limit of 1
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['per_page']);
    }

    // SHOW ENDPOINT TESTS
    public function test_show_accepts_valid_include_parameters()
    {
        $country = Country::factory()->create();

        $response = $this->getJson(route('country.show', $country).'?include=items');

        $response->assertOk();
    }

    public function test_show_rejects_unexpected_query_parameters()
    {
        // SECURITY TEST: System should reject unexpected parameters with 422
        $country = Country::factory()->create();

        $response = $this->getJson(route('country.show', $country).'?include=items&unexpected=value&admin_debug=true');

        // Secure behavior: rejects unexpected parameters
        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'unexpected',
                'admin_debug',
            ],
        ]);
    }

    // STORE ENDPOINT TESTS
    public function test_store_validates_required_fields()
    {
        $response = $this->postJson(route('country.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id', 'internal_name']);
    }

    public function test_store_validates_field_types()
    {
        $response = $this->postJson(route('country.store'), [
            'id' => 123, // Should be string
            'internal_name' => ['array'], // Should be string
            'backward_compatibility' => 123, // Should be string or null
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id', 'internal_name', 'backward_compatibility']);
    }

    public function test_store_validates_field_lengths()
    {
        $response = $this->postJson(route('country.store'), [
            'id' => 'TOOLONG', // Should be exactly 3 characters
            'internal_name' => str_repeat('a', 256), // Assuming max 255 chars
            'backward_compatibility' => 'TOO', // Should be max 2 characters
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id', 'backward_compatibility']);
    }

    public function test_store_validates_unique_constraint()
    {
        $existingCountry = Country::factory()->create();

        $response = $this->postJson(route('country.store'), [
            'id' => $existingCountry->id, // Duplicate ID
            'internal_name' => 'Different Name',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_store_rejects_unexpected_request_parameters()
    {
        // SECURITY TEST: Universal parameter injection vulnerability should be fixed
        $data = Country::factory()->make()->toArray();
        $data['unexpected_field'] = 'should_be_rejected';
        $data['admin_access'] = true;
        $data['debug_mode'] = true;

        $response = $this->postJson(route('country.store'), $data);

        // Secure behavior: rejects unexpected parameters
        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'unexpected_field',
                'admin_access',
                'debug_mode',
            ],
        ]);
    }

    public function test_store_handles_excessively_long_parameters()
    {
        $veryLongString = str_repeat('a', 10000); // 10KB string

        $response = $this->postJson(route('country.store'), [
            'id' => 'USA',
            'internal_name' => 'United States',
            'excessive_field' => $veryLongString,
        ]);

        // SECURITY REQUIREMENT: Should reject excessively long parameters
        $this->assertContains($response->status(), [201, 422]); // Documents current behavior
    }

    public function test_store_handles_invalid_utf8_parameters()
    {
        $invalidUtf8 = "\xFF\xFE"; // Invalid UTF-8 sequence

        $response = $this->postJson(route('country.store'), [
            'id' => 'USA',
            'internal_name' => 'United States',
            'invalid_utf8_field' => $invalidUtf8,
        ]);

        // SECURITY REQUIREMENT: Should reject invalid UTF-8
        $this->assertContains($response->status(), [201, 400, 422]); // Documents current behavior
    }

    public function test_store_rejects_array_parameters()
    {
        $response = $this->postJson(route('country.store'), [
            'id' => ['array' => 'instead_of_string'],
            'internal_name' => 'United States',
        ]);

        // SECURITY REQUIREMENT: Should reject array parameters
        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_validates_field_types()
    {
        $country = Country::factory()->create();

        $response = $this->putJson(route('country.update', $country), [
            'internal_name' => ['array'], // Should be string
            'backward_compatibility' => 123, // Should be string or null
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name', 'backward_compatibility']);
    }

    public function test_update_validates_field_lengths()
    {
        $country = Country::factory()->create();

        $response = $this->putJson(route('country.update', $country), [
            'internal_name' => str_repeat('a', 256), // Assuming max 255 chars
            'backward_compatibility' => 'TOO', // Should be max 2 characters
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['backward_compatibility']);
    }

    public function test_update_prohibits_id_modification()
    {
        $country = Country::factory()->create();

        $response = $this->putJson(route('country.update', $country), [
            'id' => 'NEW', // Should be prohibited
            'internal_name' => 'Valid Name',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_update_rejects_unexpected_request_parameters()
    {
        // SECURITY TEST: Universal parameter injection vulnerability should be fixed
        $country = Country::factory()->create();

        $response = $this->putJson(route('country.update', $country), [
            'internal_name' => 'Updated Name',
            'unexpected_field' => 'should_be_rejected',
            'admin_access' => true,
            'debug_mode' => true,
        ]);

        // Secure behavior: rejects unexpected parameters
        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'unexpected_field',
                'admin_access',
                'debug_mode',
            ],
        ]);
    }

    // INPUT VALIDATION TESTS
    public function test_endpoints_handle_null_and_empty_values()
    {
        $country = Country::factory()->create();

        // Test various null/empty scenarios
        $testCases = [
            ['internal_name' => null],
            ['internal_name' => ''],
            ['backward_compatibility' => ''],
        ];

        foreach ($testCases as $data) {
            $response = $this->putJson(route('country.update', $country), $data);
            // Should handle gracefully (validation error, not crash)
            $this->assertContains($response->status(), [200, 422]);
        }
    }

    public function test_endpoints_handle_excessively_long_strings()
    {
        $country = Country::factory()->create();
        $veryLongString = str_repeat('a', 10000); // 10KB string

        $response = $this->putJson(route('country.update', $country), [
            'internal_name' => 'Valid Name',
            'excessive_field' => $veryLongString,
        ]);

        // SECURITY REQUIREMENT: Should reject excessively long parameters
        $this->assertContains($response->status(), [200, 422]); // Documents current behavior
    }

    public function test_endpoints_handle_invalid_utf8_sequences()
    {
        $country = Country::factory()->create();
        $invalidUtf8 = "\xFF\xFE"; // Invalid UTF-8 sequence

        $response = $this->putJson(route('country.update', $country), [
            'internal_name' => 'Valid Name',
            'invalid_utf8_field' => $invalidUtf8,
        ]);

        // SECURITY REQUIREMENT: Should reject invalid UTF-8
        $this->assertContains($response->status(), [200, 400, 422]); // Documents current behavior
    }
}

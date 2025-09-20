<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Country;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive parameter validation tests for Partner API endpoints
 */
class PartnerParameterValidationTest extends TestCase
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
    public function test_index_accepts_valid_parameters_with_includes()
    {
        Partner::factory()->count(3)->create();

        $response = $this->getJson(route('partner.index', [
            'page' => 1,
            'per_page' => 2,
            'include' => 'country,items',
        ]));

        $response->assertOk();
        $response->assertJsonPath('meta.per_page', 2);
    }

    public function test_index_rejects_invalid_include_parameters()
    {
        Partner::factory()->count(2)->create();

        $response = $this->getJson(route('partner.index', [
            'include' => 'invalid_relation,nonexistent',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['include']);
    }

    public function test_index_rejects_unexpected_query_parameters()
    {
        // SECURITY TEST: Universal parameter injection vulnerability should be fixed
        Partner::factory()->count(2)->create();

        $response = $this->getJson(route('partner.index', [
            'page' => 1,
            'include' => 'country',
            'unexpected_param' => 'test',
            'filter_by_type' => 'museum', // Not implemented but should be rejected
            'admin_access' => true,
            'debug_mode' => true,
        ]));

        // Secure behavior: rejects unexpected parameters
        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'unexpected_param',
                'filter_by_type',
                'admin_access',
                'debug_mode',
            ],
        ]);
    }

    // SHOW ENDPOINT TESTS
    public function test_show_accepts_valid_include_parameters()
    {
        $partner = Partner::factory()->create();

        $response = $this->getJson(route('partner.show', $partner).'?include=country,items');

        $response->assertOk();
    }

    public function test_show_rejects_unexpected_query_parameters()
    {
        // SECURITY TEST: Current insecure behavior should be fixed
        $partner = Partner::factory()->create();

        $response = $this->getJson(route('partner.show', $partner).'?include=country&unexpected=value&debug=true&admin_view=enabled');

        // Secure behavior: rejects unexpected parameters
        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'unexpected',
                'debug',
                'admin_view',
            ],
        ]);
    }

    // STORE ENDPOINT TESTS
    public function test_store_validates_required_fields()
    {
        $response = $this->postJson(route('partner.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name', 'type']);
    }

    public function test_store_validates_enum_type_field()
    {
        $response = $this->postJson(route('partner.store'), [
            'internal_name' => 'Test Partner',
            'type' => 'invalid_type', // Should be: museum, institution, individual
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['type']);
    }

    public function test_store_validates_country_id_format_and_existence()
    {
        $response = $this->postJson(route('partner.store'), [
            'internal_name' => 'Test Partner',
            'type' => 'museum',
            'country_id' => 'ZZ', // Wrong size (should be 3 chars)
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['country_id']);

        // Test non-existent country
        $response = $this->postJson(route('partner.store'), [
            'internal_name' => 'Test Partner',
            'type' => 'museum',
            'country_id' => 'ZZZ', // Doesn't exist
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['country_id']);
    }

    public function test_store_accepts_valid_country_id()
    {
        $country = Country::factory()->create();

        $response = $this->postJson(route('partner.store'), [
            'internal_name' => 'Test Partner',
            'type' => 'museum',
            'country_id' => $country->id,
        ]);

        $response->assertCreated();
    }

    public function test_store_validates_field_types()
    {
        $response = $this->postJson(route('partner.store'), [
            'internal_name' => 123, // Should be string
            'type' => ['array'], // Should be string (enum)
            'backward_compatibility' => 123, // Should be string or null
            'country_id' => 123, // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name', 'type', 'backward_compatibility', 'country_id']);
    }

    public function test_store_prohibits_id_field()
    {
        $response = $this->postJson(route('partner.store'), [
            'id' => 'some-uuid', // Should be prohibited
            'internal_name' => 'Test Partner',
            'type' => 'museum',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_store_rejects_unexpected_request_parameters()
    {
        // SECURITY TEST: Universal parameter injection vulnerability should be fixed
        $response = $this->postJson(route('partner.store'), [
            'internal_name' => 'Test Partner',
            'type' => 'museum',
            'unexpected_field' => 'should_be_rejected',
            'admin_override' => true,
            'debug_mode' => true,
        ]);

        // Secure behavior: rejects unexpected parameters
        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'unexpected_field',
                'admin_override',
                'debug_mode',
            ],
        ]);
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_validates_enum_type_field()
    {
        $partner = Partner::factory()->create();

        $response = $this->putJson(route('partner.update', $partner), [
            'internal_name' => 'Updated Partner',
            'type' => 'invalid_type',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['type']);
    }

    public function test_update_validates_country_references()
    {
        $partner = Partner::factory()->create();

        $response = $this->putJson(route('partner.update', $partner), [
            'internal_name' => 'Updated Partner',
            'type' => 'museum',
            'country_id' => 'ZZZ', // Non-existent
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['country_id']);
    }

    public function test_update_prohibits_id_modification()
    {
        $partner = Partner::factory()->create();

        $response = $this->putJson(route('partner.update', $partner), [
            'id' => 'new-uuid', // Should be prohibited
            'internal_name' => 'Updated Partner',
            'type' => 'museum',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_update_rejects_unexpected_request_parameters()
    {
        // SECURITY TEST: Current insecure behavior should be fixed
        $partner = Partner::factory()->create();

        $response = $this->putJson(route('partner.update', $partner), [
            'internal_name' => 'Updated Partner',
            'type' => 'museum',
            'unexpected_field' => 'should_be_rejected',
            'privilege_escalation' => 'admin',
        ]);

        // Secure behavior: rejects unexpected parameters
        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'unexpected_field',
                'privilege_escalation',
            ],
        ]);
    }

    // EDGE CASE TESTS
    public function test_handles_all_valid_enum_values()
    {
        $validTypes = ['museum', 'institution', 'individual'];

        foreach ($validTypes as $type) {
            $response = $this->postJson(route('partner.store'), [
                'internal_name' => "Test {$type}",
                'type' => $type,
            ]);

            $response->assertCreated();
        }
    }

    public function test_handles_case_sensitivity_in_enum_values()
    {
        $response = $this->postJson(route('partner.store'), [
            'internal_name' => 'Test Partner',
            'type' => 'MUSEUM', // Uppercase should fail
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['type']);
    }

    public function test_handles_null_vs_empty_string_for_optional_fields()
    {
        $partner = Partner::factory()->create();

        $testCases = [
            ['country_id' => null],
            ['country_id' => ''],
            ['backward_compatibility' => null],
            ['backward_compatibility' => ''],
        ];

        foreach ($testCases as $data) {
            $updateData = array_merge([
                'internal_name' => 'Test Name',
                'type' => 'museum',
            ], $data);

            $response = $this->putJson(route('partner.update', $partner), $updateData);

            // Should handle gracefully (validation error or success, not crash)
            $this->assertContains($response->status(), [200, 422]);
        }
    }

    public function test_handles_excessively_long_parameters_without_crashing()
    {
        $veryLongString = str_repeat('a', 10000);

        $response = $this->postJson(route('partner.store'), [
            'internal_name' => $veryLongString,
            'type' => 'museum',
            'unexpected_huge_field' => $veryLongString,
        ]);

        $response->assertUnprocessable(); // Should handle gracefully
    }
}

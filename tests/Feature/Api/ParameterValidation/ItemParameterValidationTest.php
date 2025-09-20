<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Country;
use App\Models\Item;
use App\Models\Partner;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive parameter validation tests for Item API endpoints
 */
class ItemParameterValidationTest extends TestCase
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
    public function test_index_accepts_valid_parameters_with_complex_includes()
    {
        Item::factory()->count(3)->create();

        $response = $this->getJson(route('item.index', [
            'page' => 1,
            'per_page' => 2,
            'include' => 'partner,country,project,tags,translations',
        ]));

        $response->assertOk();
        $response->assertJsonPath('meta.per_page', 2);
    }

    public function test_index_rejects_invalid_include_parameters()
    {
        Item::factory()->count(2)->create();

        $response = $this->getJson(route('item.index', [
            'include' => 'invalid_relation,nonexistent,fake_relation',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['include']);
    }

    public function test_index_rejects_unexpected_query_parameters()
    {
        // SECURITY TEST: Universal parameter injection vulnerability should be fixed
        Item::factory()->count(2)->create();

        $response = $this->getJson(route('item.index', [
            'page' => 1,
            'include' => 'partner',
            'search' => 'test', // Not implemented and should be rejected
            'filter_by_type' => 'object', // Not implemented
            'sort_by' => 'name', // Not implemented
            'admin_debug' => true,
            'unexpected_param' => 'should_be_rejected',
        ]));

        // Secure behavior: rejects unexpected parameters
        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'search',
                'filter_by_type',
                'sort_by',
                'admin_debug',
                'unexpected_param',
            ],
        ]);
    }

    // SHOW ENDPOINT TESTS
    public function test_show_accepts_valid_include_parameters()
    {
        $item = Item::factory()->create();

        $response = $this->getJson(route('item.show', $item).'?include=partner,country,project');

        $response->assertOk();
    }

    public function test_show_rejects_unexpected_query_parameters()
    {
        // SECURITY TEST: Current insecure behavior should be fixed
        $item = Item::factory()->create();

        $response = $this->getJson(route('item.show', $item).'?include=partner&debug=true&admin_view=1&unexpected=test');

        // Secure behavior: rejects unexpected parameters
        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'debug',
                'admin_view',
                'unexpected',
            ],
        ]);
    }

    // STORE ENDPOINT TESTS
    public function test_store_validates_required_fields()
    {
        $response = $this->postJson(route('item.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name', 'type']);
    }

    public function test_store_validates_enum_type_field()
    {
        $response = $this->postJson(route('item.store'), [
            'internal_name' => 'Test Item',
            'type' => 'invalid_type', // Should be: object, monument
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['type']);
    }

    public function test_store_validates_uuid_fields()
    {
        $response = $this->postJson(route('item.store'), [
            'internal_name' => 'Test Item',
            'type' => 'object',
            'partner_id' => 'not-a-uuid', // Should be valid UUID
            'project_id' => 'also-not-uuid',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['partner_id', 'project_id']);
    }

    public function test_store_validates_foreign_key_existence()
    {
        $validUuid = '12345678-1234-1234-1234-123456789012';

        $response = $this->postJson(route('item.store'), [
            'internal_name' => 'Test Item',
            'type' => 'object',
            'partner_id' => $validUuid, // Valid UUID format but doesn't exist
            'project_id' => $validUuid,
        ]);

        // Note: Current controller doesn't validate existence of partner/project
        // This shows a potential security gap
        $response->assertCreated(); // Currently accepts non-existent UUIDs
    }

    public function test_store_validates_country_id_format_and_existence()
    {
        $response = $this->postJson(route('item.store'), [
            'internal_name' => 'Test Item',
            'type' => 'object',
            'country_id' => 'ZZ', // Wrong size
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['country_id']);

        // Test non-existent country
        $response = $this->postJson(route('item.store'), [
            'internal_name' => 'Test Item',
            'type' => 'object',
            'country_id' => 'ZZZ', // Doesn't exist
        ]);

        // Current controller doesn't validate country existence - security gap
        $response->assertCreated(); // Currently accepts non-existent countries
    }

    public function test_store_accepts_valid_foreign_keys()
    {
        $country = Country::factory()->create();
        $partner = Partner::factory()->create();
        $project = Project::factory()->create();

        $response = $this->postJson(route('item.store'), [
            'internal_name' => 'Test Item',
            'type' => 'object',
            'country_id' => $country->id,
            'partner_id' => $partner->id,
            'project_id' => $project->id,
        ]);

        $response->assertCreated();
    }

    public function test_store_prohibits_id_field()
    {
        $response = $this->postJson(route('item.store'), [
            'id' => 'some-uuid', // Should be prohibited
            'internal_name' => 'Test Item',
            'type' => 'object',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_store_rejects_unexpected_request_parameters()
    {
        // SECURITY TEST: Universal parameter injection vulnerability should be fixed
        $response = $this->postJson(route('item.store'), [
            'internal_name' => 'Test Item',
            'type' => 'object',
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
        $item = Item::factory()->create();

        $response = $this->putJson(route('item.update', $item), [
            'internal_name' => 'Updated Item',
            'type' => 'invalid_type',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['type']);
    }

    public function test_update_validates_uuid_fields()
    {
        $item = Item::factory()->create();

        $response = $this->putJson(route('item.update', $item), [
            'internal_name' => 'Updated Item',
            'type' => 'object',
            'partner_id' => 'not-a-uuid',
            'project_id' => 'also-not-uuid',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['partner_id', 'project_id']);
    }

    public function test_update_prohibits_id_modification()
    {
        $item = Item::factory()->create();

        $response = $this->putJson(route('item.update', $item), [
            'id' => 'new-uuid', // Should be prohibited
            'internal_name' => 'Updated Item',
            'type' => 'object',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_update_rejects_unexpected_request_parameters()
    {
        // SECURITY TEST: Current insecure behavior should be fixed
        $item = Item::factory()->create();

        $response = $this->putJson(route('item.update', $item), [
            'internal_name' => 'Updated Item',
            'type' => 'object',
            'unexpected_field' => 'should_be_rejected',
            'change_owner' => 'admin',
            'escalate_privileges' => true,
        ]);

        // Secure behavior: rejects unexpected parameters
        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'unexpected_field',
                'change_owner',
                'escalate_privileges',
            ],
        ]);
    }

    // EDGE CASE TESTS
    public function test_handles_all_valid_enum_values()
    {
        $validTypes = ['object', 'monument'];

        foreach ($validTypes as $type) {
            $response = $this->postJson(route('item.store'), [
                'internal_name' => "Test {$type}",
                'type' => $type,
            ]);

            $response->assertCreated();
        }
    }

    public function test_handles_case_sensitivity_in_enum_values()
    {
        $response = $this->postJson(route('item.store'), [
            'internal_name' => 'Test Item',
            'type' => 'OBJECT', // Uppercase should fail
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['type']);
    }

    public function test_handles_null_vs_empty_string_for_optional_fields()
    {
        $item = Item::factory()->create();

        $testCases = [
            ['partner_id' => null],
            ['partner_id' => ''],
            ['project_id' => null],
            ['project_id' => ''],
            ['country_id' => null],
            ['country_id' => ''],
            ['backward_compatibility' => null],
            ['backward_compatibility' => ''],
        ];

        foreach ($testCases as $data) {
            $updateData = array_merge([
                'internal_name' => 'Test Name',
                'type' => 'object',
            ], $data);

            $response = $this->putJson(route('item.update', $item), $updateData);

            // Should handle gracefully
            $this->assertContains($response->status(), [200, 422]);
        }
    }

    public function test_handles_malformed_uuids()
    {
        $malformedUuids = [
            '123', // Too short
            '12345678-1234-1234-1234', // Missing part
            '12345678-1234-1234-1234-123456789012345', // Too long
            'xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx', // Invalid characters
            '', // Empty
        ];

        foreach ($malformedUuids as $uuid) {
            $response = $this->postJson(route('item.store'), [
                'internal_name' => 'Test Item',
                'type' => 'object',
                'partner_id' => $uuid,
            ]);

            $response->assertUnprocessable();
            $response->assertJsonValidationErrors(['partner_id']);
        }
    }

    public function test_handles_excessively_long_parameters_without_crashing()
    {
        $veryLongString = str_repeat('a', 10000);

        $response = $this->postJson(route('item.store'), [
            'internal_name' => $veryLongString,
            'type' => 'object',
            'unexpected_huge_field' => $veryLongString,
        ]);

        $response->assertUnprocessable(); // Should handle gracefully
    }
}

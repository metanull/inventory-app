<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Context;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive parameter validation tests for Context API endpoints
 */
class ContextParameterValidationTest extends TestCase
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
        Context::factory()->count(5)->create();

        $response = $this->getJson(route('context.index', [
            'page' => 2,
            'per_page' => 3,
        ]));

        $response->assertOk();
        $response->assertJsonPath('meta.current_page', 2);
        $response->assertJsonPath('meta.per_page', 3);
    }

    public function test_index_rejects_invalid_include_parameters()
    {
        Context::factory()->count(2)->create();

        $response = $this->getJson(route('context.index', [
            'include' => 'invalid_relation,nonexistent,fake_includes',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['include']);
    }

    public function test_index_rejects_unexpected_query_parameters_currently()
    {
        Context::factory()->count(2)->create();

        $response = $this->getJson(route('context.index', [
            'page' => 1,
            'include' => 'translations',
            'filter_by_type' => 'historical', // Not implemented
            'search_keyword' => 'ancient', // Not implemented
            'sort_order' => 'desc', // Not implemented
            'admin_override' => true,
            'debug_queries' => true,
            'export_type' => 'json',
            'sensitive_data' => 'access_granted',
        ]));

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['filter_by_type']);
    }

    // SHOW ENDPOINT TESTS
    public function test_show_rejects_unexpected_query_parameters_currently()
    {
        $context = Context::factory()->create();

        $response = $this->getJson(route('context.show', $context).'?include=translations&admin_mode=1&debug=verbose&internal_view=true');

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['admin_mode']);
    }

    // STORE ENDPOINT TESTS
    public function test_store_validates_required_fields()
    {
        $response = $this->postJson(route('context.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_store_validates_unique_internal_name()
    {
        $existingContext = Context::factory()->create();

        $response = $this->postJson(route('context.store'), [
            'internal_name' => $existingContext->internal_name,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_store_prohibits_id_field()
    {
        $response = $this->postJson(route('context.store'), [
            'id' => 'some-uuid', // Should be prohibited
            'internal_name' => 'Test Context',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_store_accepts_optional_backward_compatibility_field()
    {
        $response = $this->postJson(route('context.store'), [
            'internal_name' => 'Legacy Context',
            'backward_compatibility' => 'old_context_456',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.backward_compatibility', 'old_context_456');
    }

    public function test_store_rejects_unexpected_request_parameters_currently()
    {
        $response = $this->postJson(route('context.store'), [
            'internal_name' => 'Test Context',
            'unexpected_field' => 'should_be_rejected',
            'priority' => 'high', // Not implemented
            'visibility' => 'public', // Not implemented
            'admin_notes' => 'secret information',
            'escalation_level' => '5',
            'admin_access' => true,
            'debug_mode' => true,
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_validates_unique_internal_name()
    {
        $context1 = Context::factory()->create();
        $context2 = Context::factory()->create();

        $response = $this->putJson(route('context.update', $context1), [
            'internal_name' => $context2->internal_name,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_update_prohibits_id_modification()
    {
        $context = Context::factory()->create();

        $response = $this->putJson(route('context.update', $context), [
            'id' => 'new-uuid', // Should be prohibited
            'internal_name' => 'Updated Context',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_update_accepts_same_internal_name()
    {
        $context = Context::factory()->create();

        $response = $this->putJson(route('context.update', $context), [
            'internal_name' => $context->internal_name, // Same name should be allowed
        ]);

        $response->assertOk();
    }

    public function test_update_rejects_unexpected_request_parameters_currently()
    {
        $context = Context::factory()->create();

        $response = $this->putJson(route('context.update', $context), [
            'internal_name' => 'Updated Context',
            'unexpected_field' => 'should_be_rejected',
            'change_priority' => 'urgent',
            'assign_reviewer' => 'admin',
            'budget_allocation' => '50000',
            'security_clearance' => 'top_secret',
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // EDGE CASE TESTS
    public function test_handles_null_vs_empty_string_for_optional_fields()
    {
        $context = Context::factory()->create();

        $testCases = [
            ['backward_compatibility' => null],
            ['backward_compatibility' => ''],
        ];

        foreach ($testCases as $data) {
            $updateData = array_merge([
                'internal_name' => 'Test Context Update',
            ], $data);

            $response = $this->putJson(route('context.update', $context), $updateData);

            $response->assertOk(); // Should handle gracefully
        }
    }

    public function test_handles_unicode_characters_in_internal_name()
    {
        $unicodeNames = [
            'Context ñ España',
            'Contexte français',
            'Kontekst с кириллицей',
            'コンテキスト日本語',
            'سياق عربي',
            'Κείμενο ελληνικά',
            'Contesto italiano',
        ];

        foreach ($unicodeNames as $name) {
            $response = $this->postJson(route('context.store'), [
                'internal_name' => $name,
            ]);

            $response->assertCreated(); // Should handle Unicode gracefully
        }
    }

    public function test_handles_very_long_internal_name()
    {
        $veryLongName = str_repeat('Very Long Context Name ', 100); // Very long string

        $response = $this->postJson(route('context.store'), [
            'internal_name' => $veryLongName,
        ]);

        // Should handle gracefully - either accept (if no length limit) or reject with validation
        $this->assertContains($response->status(), [201, 422]);
    }

    public function test_handles_special_characters_in_internal_name()
    {
        $specialCharNames = [
            'Context "With Quotes"',
            "Context 'With Apostrophes'",
            'Context & Ampersand',
            'Context <With> Tags',
            'Context @ Symbol',
            'Context # Hash',
            'Context % Percent',
            'Context $ Dollar',
            'Context * Asterisk',
            'Context + Plus',
            'Context = Equals',
            'Context | Pipe',
            'Context \\ Backslash',
            'Context / Forward Slash',
        ];

        foreach ($specialCharNames as $name) {
            $response = $this->postJson(route('context.store'), [
                'internal_name' => $name,
            ]);

            // Should handle gracefully
            $this->assertContains($response->status(), [201, 422]);
        }
    }

    public function test_pagination_edge_cases()
    {
        Context::factory()->count(15)->create();

        // Test edge cases for pagination
        $testCases = [
            ['page' => 0], // Invalid
            ['page' => -1], // Invalid
            ['page' => 'abc'], // Invalid type
            ['per_page' => 0], // Invalid
            ['per_page' => 101], // Exceeds maximum
            ['per_page' => -5], // Invalid
            ['per_page' => 'xyz'], // Invalid type
        ];

        foreach ($testCases as $params) {
            $response = $this->getJson(route('context.index', $params));
            $response->assertUnprocessable();
        }
    }

    public function test_handles_array_injection_in_scalar_fields()
    {
        // Test what happens when arrays are passed to scalar fields
        $response = $this->postJson(route('context.store'), [
            'internal_name' => ['array' => 'instead_of_string'],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }
}

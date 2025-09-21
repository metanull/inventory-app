<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive parameter validation tests for Project API endpoints
 */
class ProjectParameterValidationTest extends TestCase
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
        Project::factory()->count(5)->create();

        $response = $this->getJson(route('project.index', [
            'page' => 2,
            'per_page' => 2,
        ]));

        $response->assertOk();
        $response->assertJsonPath('meta.current_page', 2);
        $response->assertJsonPath('meta.per_page', 2);
    }

    public function test_index_rejects_invalid_include_parameters()
    {
        Project::factory()->count(2)->create();

        $response = $this->getJson(route('project.index', [
            'include' => 'invalid_relation,fake_data,non_existent',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['include']);
    }

    public function test_index_rejects_unexpected_query_parameters_currently()
    {
        // SECURITY TEST: Validates Form Request security with parameter whitelisting
        Project::factory()->count(2)->create();

        $response = $this->getJson(route('project.index', [
            'page' => 1,
            'include' => 'partners',
            'search' => 'test_project', // Not implemented parameter
            'status' => 'active', // Not implemented
            'date_from' => '2024-01-01', // Not implemented
            'date_to' => '2024-12-31', // Not implemented
            'admin_secret' => 'bypass123',
            'debug_mode' => true,
            'export_format' => 'csv',
        ]));

        $response->assertStatus(422); // Must reject unexpected params for security
        $response->assertJsonValidationErrors([
            'search', 'status', 'date_from', 'date_to', 'admin_secret', 'debug_mode', 'export_format',
        ]);
    }

    // SHOW ENDPOINT TESTS
    public function test_show_rejects_unexpected_query_parameters_currently()
    {
        // SECURITY TEST: Validates Form Request security with parameter whitelisting
        $project = Project::factory()->create();

        $response = $this->getJson(route('project.show', $project).'?include=partners&admin_view=1&debug=true&internal_notes=show');

        $response->assertStatus(422); // Must reject unexpected params for security
        $response->assertJsonValidationErrors(['admin_view', 'debug', 'internal_notes']);
    }

    // STORE ENDPOINT TESTS
    public function test_store_validates_required_fields()
    {
        $response = $this->postJson(route('project.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_store_validates_unique_internal_name()
    {
        $existingProject = Project::factory()->create();

        $response = $this->postJson(route('project.store'), [
            'internal_name' => $existingProject->internal_name,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_store_prohibits_id_field()
    {
        $response = $this->postJson(route('project.store'), [
            'id' => 'some-uuid', // Should be prohibited
            'internal_name' => 'Test Project',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_store_accepts_valid_data()
    {
        $response = $this->postJson(route('project.store'), [
            'internal_name' => 'Test Project 2024',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.internal_name', 'Test Project 2024');
    }

    public function test_store_accepts_optional_backward_compatibility_field()
    {
        $response = $this->postJson(route('project.store'), [
            'internal_name' => 'Legacy Project',
            'backward_compatibility' => 'old_project_123',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.backward_compatibility', 'old_project_123');
    }

    public function test_store_rejects_unexpected_request_parameters_currently()
    {
        // SECURITY TEST: Universal parameter injection vulnerability protection
        $response = $this->postJson(route('project.store'), [
            'internal_name' => 'Test Project',
            'unexpected_field' => 'should_be_rejected',
            'admin_flag' => true,
            'debug_mode' => true,
        ]);

        $response->assertStatus(422); // Must reject unexpected params for security
        $response->assertJsonValidationErrors(['unexpected_field', 'admin_flag', 'debug_mode']);
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_validates_unique_internal_name()
    {
        $project1 = Project::factory()->create();
        $project2 = Project::factory()->create();

        $response = $this->putJson(route('project.update', $project1), [
            'internal_name' => $project2->internal_name,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_update_prohibits_id_modification()
    {
        $project = Project::factory()->create();

        $response = $this->putJson(route('project.update', $project), [
            'id' => 'new-uuid', // Should be prohibited
            'internal_name' => 'Updated Project',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_update_accepts_valid_data()
    {
        $project = Project::factory()->create();

        $response = $this->putJson(route('project.update', $project), [
            'internal_name' => 'Updated Project Name',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.internal_name', 'Updated Project Name');
    }

    public function test_update_accepts_same_internal_name()
    {
        $project = Project::factory()->create();

        $response = $this->putJson(route('project.update', $project), [
            'internal_name' => $project->internal_name, // Same name should be allowed
        ]);

        $response->assertOk();
    }

    public function test_update_rejects_unexpected_request_parameters_currently()
    {
        // SECURITY TEST: Current behavior must reject unexpected parameters
        $project = Project::factory()->create();

        $response = $this->putJson(route('project.update', $project), [
            'internal_name' => 'Updated Project',
            'unexpected_field' => 'should_be_rejected',
            'change_status' => 'archived',
            'escalate_priority' => 'urgent',
            'assign_new_owner' => 'admin',
            'budget_increase' => '500000',
        ]);

        $response->assertStatus(422); // Must reject unexpected params for security
        $response->assertJsonValidationErrors([
            'unexpected_field', 'change_status', 'escalate_priority', 'assign_new_owner', 'budget_increase',
        ]);
    }

    // EDGE CASE TESTS
    public function test_handles_null_vs_empty_string_for_optional_fields()
    {
        $project = Project::factory()->create();

        $testCases = [
            ['backward_compatibility' => null],
            ['backward_compatibility' => ''],
        ];

        foreach ($testCases as $data) {
            $updateData = array_merge([
                'internal_name' => 'Test Name Update',
            ], $data);

            $response = $this->putJson(route('project.update', $project), $updateData);

            $response->assertOk(); // Should handle gracefully
        }
    }

    public function test_handles_excessively_long_internal_name()
    {
        $veryLongName = str_repeat('Project Name ', 1000); // Very long string

        $response = $this->postJson(route('project.store'), [
            'internal_name' => $veryLongName,
        ]);

        // Should handle gracefully - either accept (if no length limit) or reject with validation
        $this->assertContains($response->status(), [201, 422]);
    }

    public function test_handles_special_characters_in_internal_name()
    {
        $specialCharNames = [
            'Project "With Quotes"',
            "Project 'With Apostrophes'",
            'Project & Ampersand',
            'Project <With> Tags',
            'Projeto ção (Portuguese)',
            'プロジェクト (Japanese)',
            'Проект (Russian)',
            'Project 2024 #1',
            'Project @ Symbol',
            'Project % Percent',
        ];

        foreach ($specialCharNames as $name) {
            $response = $this->postJson(route('project.store'), [
                'internal_name' => $name,
            ]);

            // Should handle gracefully
            $this->assertContains($response->status(), [201, 422]);
        }
    }

    public function test_handles_whitespace_only_internal_name()
    {
        $whitespaceNames = [
            '   ', // Spaces only
            "\t\t", // Tabs only
            "\n\n", // Newlines only
            " \t \n ", // Mixed whitespace
        ];

        foreach ($whitespaceNames as $name) {
            $response = $this->postJson(route('project.store'), [
                'internal_name' => $name,
            ]);

            $response->assertUnprocessable(); // Should reject whitespace-only names
            $response->assertJsonValidationErrors(['internal_name']);
        }
    }

    public function test_handles_numeric_only_internal_name()
    {
        $response = $this->postJson(route('project.store'), [
            'internal_name' => '12345',
        ]);

        $response->assertCreated(); // Numeric names should be allowed
    }

    public function test_pagination_boundary_conditions()
    {
        Project::factory()->count(10)->create();

        // Test maximum per_page
        $response = $this->getJson(route('project.index', [
            'per_page' => 100,
        ]));
        $response->assertOk();

        // Test minimum per_page
        $response = $this->getJson(route('project.index', [
            'per_page' => 1,
        ]));
        $response->assertOk();

        // Test invalid per_page values
        $invalidPerPage = [0, 101, -1, 'abc'];
        foreach ($invalidPerPage as $value) {
            $response = $this->getJson(route('project.index', [
                'per_page' => $value,
            ]));
            $response->assertUnprocessable();
        }
    }

    public function test_handles_malformed_json_gracefully()
    {
        // This would typically be handled by Laravel's middleware,
        // but let's verify the endpoint behavior
        $response = $this->postJson(route('project.store'), [
            'internal_name' => ['array' => 'instead_of_string'], // Wrong type
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }
}

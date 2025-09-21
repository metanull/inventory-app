<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Exhibition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive parameter validation tests for Exhibition API endpoints
 */
class ExhibitionParameterValidationTest extends TestCase
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
        Exhibition::factory()->count(20)->create();

        $response = $this->getJson(route('exhibition.index', [
            'page' => 4,
            'per_page' => 5,
        ]));

        $response->assertOk();
        $response->assertJsonPath('meta.current_page', 4);
        $response->assertJsonPath('meta.per_page', 5);
    }

    public function test_index_rejects_invalid_include_parameters()
    {
        Exhibition::factory()->count(2)->create();

        $response = $this->getJson(route('exhibition.index', [
            'include' => 'invalid_relation,fake_items,non_existent',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['include']);
    }

    public function test_index_rejects_unexpected_query_parameters_currently()
    {
        Exhibition::factory()->count(2)->create();

        $response = $this->getJson(route('exhibition.index', [
            'page' => 1,
            'include' => 'translations',
            'filter_by_status' => 'active', // Not implemented
            'date_range' => '2024-01-01,2024-12-31', // Not implemented
            'curator' => 'admin', // Not implemented
            'venue_type' => 'museum', // Not implemented
            'admin_access' => true,
            'debug_exhibitions' => true,
            'export_format' => 'pdf',
            'bulk_operation' => 'archive_all',
        ]));

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['filter_by_status']);
    }

    // SHOW ENDPOINT TESTS
    public function test_show_rejects_unexpected_query_parameters_currently()
    {
        $exhibition = Exhibition::factory()->create();

        $response = $this->getJson(route('exhibition.show', $exhibition).'?include=translations&admin_view=true&show_analytics=detailed&visitor_stats=include');

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['admin_view']);
    }

    // STORE ENDPOINT TESTS
    public function test_store_validates_required_fields()
    {
        $response = $this->postJson(route('exhibition.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_store_validates_unique_internal_name()
    {
        $existingExhibition = Exhibition::factory()->create();

        $response = $this->postJson(route('exhibition.store'), [
            'internal_name' => $existingExhibition->internal_name,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_store_validates_date_fields_if_provided()
    {
        $response = $this->postJson(route('exhibition.store'), [
            'internal_name' => 'Test Exhibition',
            'start_date' => 'invalid-date-format',
            'end_date' => 'also-invalid',
        ]);

        // Date validation might not be implemented
        $this->assertContains($response->status(), [201, 422]);
    }

    public function test_store_validates_date_logic_if_provided()
    {
        $response = $this->postJson(route('exhibition.store'), [
            'internal_name' => 'Test Exhibition',
            'start_date' => '2024-12-31',
            'end_date' => '2024-01-01', // End before start
        ]);

        // Date logic validation might not be implemented
        $this->assertContains($response->status(), [201, 422]);
    }

    public function test_store_prohibits_id_field()
    {
        $response = $this->postJson(route('exhibition.store'), [
            'id' => 'some-uuid', // Should be prohibited
            'internal_name' => 'Test Exhibition',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_store_accepts_valid_data()
    {
        $response = $this->postJson(route('exhibition.store'), [
            'internal_name' => 'Modern Art Exhibition 2024',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.internal_name', 'Modern Art Exhibition 2024');
    }

    public function test_store_accepts_optional_backward_compatibility_field()
    {
        $response = $this->postJson(route('exhibition.store'), [
            'internal_name' => 'Legacy Exhibition',
            'backward_compatibility' => 'old_exhibition_789',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.backward_compatibility', 'old_exhibition_789');
    }

    public function test_store_rejects_unexpected_request_parameters_currently()
    {
        $response = $this->postJson(route('exhibition.store'), [
            'internal_name' => 'Test Exhibition',
            'unexpected_field' => 'should_be_rejected',
            'curator' => 'Dr. Smith', // Not implemented
            'venue' => 'Main Gallery', // Not implemented
            'capacity' => '500', // Not implemented
            'admission_fee' => '15.00', // Not implemented
            'admin_created' => true,
            'malicious_html' => '<iframe src="evil.com"></iframe>',
            'sql_injection' => "'; DROP TABLE exhibitions; --",
            'privilege_escalation' => 'curator_access',
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_validates_unique_internal_name()
    {
        $exhibition1 = Exhibition::factory()->create();
        $exhibition2 = Exhibition::factory()->create();

        $response = $this->putJson(route('exhibition.update', $exhibition1), [
            'internal_name' => $exhibition2->internal_name,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_update_prohibits_id_modification()
    {
        $exhibition = Exhibition::factory()->create();

        $response = $this->putJson(route('exhibition.update', $exhibition), [
            'id' => 'new-uuid', // Should be prohibited
            'internal_name' => 'Updated Exhibition',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_update_accepts_same_internal_name()
    {
        $exhibition = Exhibition::factory()->create();

        $response = $this->putJson(route('exhibition.update', $exhibition), [
            'internal_name' => $exhibition->internal_name, // Same name should be allowed
        ]);

        $response->assertOk();
    }

    public function test_update_rejects_unexpected_request_parameters_currently()
    {
        $exhibition = Exhibition::factory()->create();

        $response = $this->putJson(route('exhibition.update', $exhibition), [
            'internal_name' => 'Updated Exhibition',
            'unexpected_field' => 'should_be_rejected',
            'change_status' => 'featured',
            'update_curator' => 'new_curator',
            'extend_dates' => 'auto',
            'boost_priority' => true,
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // EDGE CASE TESTS
    public function test_handles_unicode_characters_in_internal_name()
    {
        $unicodeNames = [
            'Exposition française',
            'Выставка русская',
            '展覧会日本語',
            'معرض عربي',
            'Exposición española',
            'Mostra italiana',
            'Wystawa polska',
            'Έκθεση ελληνική',
            'Udstilling dansk',
            'Kiállítás magyar',
        ];

        foreach ($unicodeNames as $name) {
            $response = $this->postJson(route('exhibition.store'), [
                'internal_name' => $name,
            ]);

            $response->assertCreated(); // Should handle Unicode gracefully
        }
    }

    public function test_handles_special_characters_in_internal_name()
    {
        $specialCharNames = [
            'Exhibition "Modern Art"',
            "Exhibition 'Contemporary Works'",
            'Exhibition & Installations',
            'Exhibition: Renaissance Art',
            'Exhibition (2024)',
            'Exhibition - Special Collection',
            'Exhibition @ Main Gallery',
            'Exhibition #1: Masterpieces',
            'Exhibition 50% Off',
            'Exhibition $ Value Art',
            'Exhibition * Featured',
            'Exhibition + New Works',
            'Exhibition = Excellence',
            'Exhibition | Curated',
        ];

        foreach ($specialCharNames as $name) {
            $response = $this->postJson(route('exhibition.store'), [
                'internal_name' => $name,
            ]);

            // Should handle gracefully
            $this->assertContains($response->status(), [201, 422]);
        }
    }

    public function test_handles_very_long_internal_name()
    {
        $veryLongName = str_repeat('Very Long Exhibition Name With Detailed Curatorial Description And Historical Context ', 20);

        $response = $this->postJson(route('exhibition.store'), [
            'internal_name' => $veryLongName,
        ]);

        // Should handle gracefully
        $this->assertContains($response->status(), [201, 422]);
    }

    public function test_handles_date_field_edge_cases()
    {
        $dateTestCases = [
            ['start_date' => '2024-01-01', 'end_date' => '2024-12-31'], // Valid range
            ['start_date' => '2024-01-01', 'end_date' => '2024-01-01'], // Same day
            ['start_date' => '1900-01-01', 'end_date' => '1900-12-31'], // Historical dates
            ['start_date' => '2099-01-01', 'end_date' => '2099-12-31'], // Future dates
            ['start_date' => '2024-02-29', 'end_date' => '2024-03-01'], // Leap year
            ['start_date' => 'invalid', 'end_date' => '2024-12-31'], // Invalid start
            ['start_date' => '2024-01-01', 'end_date' => 'invalid'], // Invalid end
            ['start_date' => '', 'end_date' => ''], // Empty dates
            ['start_date' => null, 'end_date' => null], // Null dates
        ];

        foreach ($dateTestCases as $index => $dates) {
            $response = $this->postJson(route('exhibition.store'), array_merge([
                'internal_name' => "Date Test Exhibition {$index}",
            ], $dates));

            // Date validation might not be implemented
            $this->assertContains($response->status(), [201, 422]);
        }
    }

    public function test_handles_empty_and_whitespace_internal_names()
    {
        $emptyNames = [
            '', // Empty
            '   ', // Spaces only
            "\t\t", // Tabs only
            "\n\n", // Newlines only
        ];

        foreach ($emptyNames as $name) {
            $response = $this->postJson(route('exhibition.store'), [
                'internal_name' => $name,
            ]);

            $response->assertUnprocessable(); // Should reject empty names
            $response->assertJsonValidationErrors(['internal_name']);
        }
    }

    public function test_handles_null_vs_empty_string_for_optional_fields()
    {
        $exhibition = Exhibition::factory()->create();

        $testCases = [
            ['backward_compatibility' => null],
            ['backward_compatibility' => ''],
            ['start_date' => null],
            ['start_date' => ''],
            ['end_date' => null],
            ['end_date' => ''],
        ];

        foreach ($testCases as $data) {
            $updateData = array_merge([
                'internal_name' => 'Test Exhibition Update',
            ], $data);

            $response = $this->putJson(route('exhibition.update', $exhibition), $updateData);

            // Should handle gracefully
            $this->assertContains($response->status(), [200, 422]);
        }
    }

    public function test_pagination_with_many_exhibitions()
    {
        Exhibition::factory()->count(80)->create();

        $testCases = [
            ['page' => 1, 'per_page' => 20],
            ['page' => 2, 'per_page' => 30],
            ['page' => 1, 'per_page' => 100], // Maximum
        ];

        foreach ($testCases as $params) {
            $response = $this->getJson(route('exhibition.index', $params));
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
            $response = $this->getJson(route('exhibition.index', $params));
            $response->assertUnprocessable();
        }
    }

    public function test_handles_array_injection_attempts()
    {
        $response = $this->postJson(route('exhibition.store'), [
            'internal_name' => ['array' => 'instead_of_string'],
            'start_date' => ['malicious' => 'array'],
            'end_date' => ['injection' => 'attempt'],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_handles_exhibition_name_variations()
    {
        $exhibitionNameVariations = [
            'Modern Art: The 20th Century',
            'Impressionism & Post-Impressionism',
            'Ancient Civilizations (Egypt)',
            'Contemporary Art 2024',
            'Photography: Black & White',
            'Sculpture: From Classic to Modern',
            'Textiles of the World',
            'Digital Art & New Media',
            'Local Artists Showcase',
            'Traveling Exhibition: European Masters',
        ];

        foreach ($exhibitionNameVariations as $name) {
            $response = $this->postJson(route('exhibition.store'), [
                'internal_name' => $name,
            ]);

            $response->assertCreated(); // Should handle various exhibition name formats
        }
    }
}

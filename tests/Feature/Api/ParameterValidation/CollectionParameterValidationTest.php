<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Collection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive parameter validation tests for Collection API endpoints
 */
class CollectionParameterValidationTest extends TestCase
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
        Collection::factory()->count(7)->create();

        $response = $this->getJson(route('collection.index', [
            'page' => 2,
            'per_page' => 3,
        ]));

        $response->assertOk();
        $response->assertJsonPath('meta.current_page', 2);
        $response->assertJsonPath('meta.per_page', 3);
    }

    public function test_index_accepts_valid_include_parameters()
    {
        Collection::factory()->count(3)->create();

        $response = $this->getJson(route('collection.index', [
            'include' => 'items,galleries,translations',
        ]));

        $response->assertOk();
    }

    public function test_index_rejects_invalid_include_parameters()
    {
        Collection::factory()->count(2)->create();

        $response = $this->getJson(route('collection.index', [
            'include' => 'invalid_relation,fake_includes,non_existent_data',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['include']);
    }

    public function test_index_rejects_unexpected_query_parameters()
    {
        // SECURITY TEST: Form Request should reject unexpected parameters
        Collection::factory()->count(2)->create();

        $response = $this->getJson(route('collection.index', [
            'page' => 1,
            'include' => 'items',
            'status' => 'published', // Not implemented
            'theme' => 'modern_art', // Not implemented
            'curator' => 'admin', // Not implemented
            'date_range' => '2024', // Not implemented
            'visibility' => 'public', // Not implemented
            'admin_mode' => true,
            'export_collection' => 'json',
            'bulk_operation' => 'archive_all',
            'secret_access' => 'granted',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'status',
                'theme',
                'curator',
                'date_range',
                'visibility',
                'admin_mode',
                'export_collection',
                'bulk_operation',
                'secret_access',
            ],
        ]);
    }

    // SHOW ENDPOINT TESTS
    public function test_show_accepts_valid_include_parameters()
    {
        $collection = Collection::factory()->create();

        $response = $this->getJson(route('collection.show', $collection).'?include=items,galleries,translations');

        $response->assertOk();
    }

    public function test_show_rejects_unexpected_query_parameters()
    {
        // SECURITY TEST: Form Request should reject unexpected parameters
        $collection = Collection::factory()->create();

        $response = $this->getJson(route('collection.show', $collection).'?include=items&full_details=true&admin_view=1&analytics=detailed');

        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'full_details',
                'admin_view',
                'analytics',
            ],
        ]);
    }

    // STORE ENDPOINT TESTS
    public function test_store_validates_required_fields()
    {
        $response = $this->postJson(route('collection.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_store_validates_unique_internal_name()
    {
        $existingCollection = Collection::factory()->create();

        $response = $this->postJson(route('collection.store'), [
            'internal_name' => $existingCollection->internal_name,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_store_prohibits_id_field()
    {
        $response = $this->postJson(route('collection.store'), [
            'id' => 'some-uuid', // Should be prohibited
            'internal_name' => 'Test Collection',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_store_accepts_valid_data()
    {
        $response = $this->postJson(route('collection.store'), [
            'internal_name' => 'Modern European Art Collection',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.internal_name', 'Modern European Art Collection');
    }

    public function test_store_accepts_optional_backward_compatibility_field()
    {
        $response = $this->postJson(route('collection.store'), [
            'internal_name' => 'Legacy Collection',
            'backward_compatibility' => 'old_collection_456',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.backward_compatibility', 'old_collection_456');
    }

    public function test_store_rejects_unexpected_request_parameters()
    {
        // SECURITY TEST: Form Request should reject unexpected parameters
        $response = $this->postJson(route('collection.store'), [
            'internal_name' => 'Test Collection',
            'unexpected_field' => 'should_be_rejected',
            'theme' => 'renaissance', // Not implemented
            'curator' => 'dr_smith', // Not implemented
            'status' => 'draft', // Not implemented
            'priority' => 'high', // Not implemented
            'budget' => '50000', // Not implemented
            'admin_override' => true,
            'malicious_payload' => '<iframe src="http://evil.com"></iframe>',
            'sql_attack' => "'; DROP TABLE collections; --",
            'escalate_privileges' => 'root',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'unexpected_field',
                'theme',
                'curator',
                'status',
                'priority',
                'budget',
                'admin_override',
                'malicious_payload',
                'sql_attack',
                'escalate_privileges',
            ],
        ]);
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_validates_unique_internal_name()
    {
        $collection1 = Collection::factory()->create();
        $collection2 = Collection::factory()->create();

        $response = $this->putJson(route('collection.update', $collection1), [
            'internal_name' => $collection2->internal_name,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_update_prohibits_id_modification()
    {
        $collection = Collection::factory()->create();

        $response = $this->putJson(route('collection.update', $collection), [
            'id' => 'new-uuid', // Should be prohibited
            'internal_name' => 'Updated Collection',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_update_accepts_same_internal_name()
    {
        $collection = Collection::factory()->create();

        $response = $this->putJson(route('collection.update', $collection), [
            'internal_name' => $collection->internal_name, // Same name should be allowed
        ]);

        $response->assertOk();
    }

    public function test_update_rejects_unexpected_request_parameters()
    {
        // SECURITY TEST: Form Request should reject unexpected parameters
        $collection = Collection::factory()->create();

        $response = $this->putJson(route('collection.update', $collection), [
            'internal_name' => 'Updated Collection',
            'unexpected_field' => 'should_be_rejected',
            'change_theme' => 'baroque',
            'reassign_curator' => 'new_admin',
            'publish_status' => 'live',
            'featured' => true,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'unexpected_field',
                'change_theme',
                'reassign_curator',
                'publish_status',
                'featured',
            ],
        ]);
    }

    // EDGE CASE TESTS
    public function test_handles_unicode_characters_in_internal_name()
    {
        $unicodeNames = [
            'Collection ñ España',
            'Collection française',
            'Коллекция кириллица',
            'コレクション日本語',
            'مجموعة عربية',
            'Συλλογή ελληνικά',
            'Collezione italiana',
            'Kolekcja polska',
            'Sammlung deutsch',
        ];

        foreach ($unicodeNames as $name) {
            $response = $this->postJson(route('collection.store'), [
                'internal_name' => $name,
            ]);

            $response->assertCreated(); // Should handle Unicode gracefully
        }
    }

    public function test_handles_very_long_internal_name()
    {
        $veryLongName = str_repeat('Very Long Collection Name With Many Words And Details ', 50); // Very long string

        $response = $this->postJson(route('collection.store'), [
            'internal_name' => $veryLongName,
        ]);

        // Should handle gracefully - either accept (if no length limit) or reject with validation
        $this->assertContains($response->status(), [201, 422]);
    }

    public function test_handles_special_characters_in_internal_name()
    {
        $specialCharNames = [
            'Collection "With Quotes"',
            "Collection 'With Apostrophes'",
            'Collection & Ampersand',
            'Collection <With> Brackets',
            'Collection @ Museum',
            'Collection # 1',
            'Collection % Modern',
            'Collection $ Value',
            'Collection * Featured',
            'Collection + Plus',
            'Collection = Equals',
            'Collection | Pipe',
            'Collection \\ Backslash',
            'Collection / Slash',
            'Collection : Colon',
            'Collection ; Semicolon',
            'Collection ? Question',
            'Collection ! Important',
            'Collection (Parentheses)',
            'Collection [Brackets]',
            'Collection {Braces}',
        ];

        foreach ($specialCharNames as $name) {
            $response = $this->postJson(route('collection.store'), [
                'internal_name' => $name,
            ]);

            // Should handle gracefully
            $this->assertContains($response->status(), [201, 422]);
        }
    }

    public function test_handles_empty_and_whitespace_variations()
    {
        $whitespaceNames = [
            '', // Empty
            '   ', // Spaces only
            "\t\t", // Tabs only
            "\n\n", // Newlines only
            " \t \n \r ", // Mixed whitespace
            '   Collection with leading spaces',
            'Collection with trailing spaces   ',
            'Collection  with  double  spaces',
            "Collection\twith\ttabs",
            "Collection\nwith\nnewlines",
        ];

        foreach ($whitespaceNames as $name) {
            $response = $this->postJson(route('collection.store'), [
                'internal_name' => $name,
            ]);

            if (trim($name) === '') {
                $response->assertUnprocessable(); // Should reject empty/whitespace-only names
                $response->assertJsonValidationErrors(['internal_name']);
            } else {
                // Should handle gracefully
                $this->assertContains($response->status(), [201, 422]);
            }
        }
    }

    public function test_handles_numeric_internal_names()
    {
        $numericNames = [
            '123',
            '0',
            '2024',
            '3.14159',
            '-42',
            '1e10',
            '0xFF', // Hex-like
            '1,000,000', // With commas
        ];

        foreach ($numericNames as $name) {
            $response = $this->postJson(route('collection.store'), [
                'internal_name' => $name,
            ]);

            $response->assertCreated(); // Numeric names should be allowed
        }
    }

    public function test_handles_json_injection_attempts()
    {
        $jsonPayloads = [
            '{"malicious": "payload"}',
            '[{"array": "injection"}]',
            'null',
            'true',
            'false',
        ];

        foreach ($jsonPayloads as $payload) {
            $response = $this->postJson(route('collection.store'), [
                'internal_name' => $payload,
            ]);

            $response->assertCreated(); // Should treat as regular strings
        }
    }

    public function test_handles_malformed_request_data()
    {
        $response = $this->postJson(route('collection.store'), [
            'internal_name' => ['array' => 'instead_of_string'], // Wrong type
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_pagination_stress_test()
    {
        Collection::factory()->count(50)->create();

        // Test various pagination scenarios
        $testCases = [
            ['page' => 1, 'per_page' => 1], // Small pages
            ['page' => 1, 'per_page' => 50], // Large pages
            ['page' => 5, 'per_page' => 10], // Middle pages
            ['page' => 1, 'per_page' => 100], // Maximum per_page
        ];

        foreach ($testCases as $params) {
            $response = $this->getJson(route('collection.index', $params));
            $response->assertOk();
        }

        // Test invalid pagination
        $invalidCases = [
            ['page' => 0],
            ['page' => -1],
            ['page' => 'abc'],
            ['per_page' => 0],
            ['per_page' => 101],
            ['per_page' => -1],
            ['per_page' => 'xyz'],
        ];

        foreach ($invalidCases as $params) {
            $response = $this->getJson(route('collection.index', $params));
            $response->assertUnprocessable();
        }
    }

    public function test_handles_concurrent_creation_attempts()
    {
        // Test creating collections with same name (should fail uniqueness)
        $sameName = 'Duplicate Collection Name';

        $response1 = $this->postJson(route('collection.store'), [
            'internal_name' => $sameName,
        ]);
        $response1->assertCreated();

        $response2 = $this->postJson(route('collection.store'), [
            'internal_name' => $sameName,
        ]);
        $response2->assertUnprocessable();
        $response2->assertJsonValidationErrors(['internal_name']);
    }
}

<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive parameter validation tests for Tag API endpoints
 */
class TagParameterValidationTest extends TestCase
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
        Tag::factory()->count(8)->create();

        $response = $this->getJson(route('tag.index', [
            'page' => 2,
            'per_page' => 3,
        ]));

        $response->assertOk();
        $response->assertJsonPath('meta.current_page', 2);
        $response->assertJsonPath('meta.per_page', 3);
    }

    public function test_index_rejects_include_parameters()
    {
        // SECURITY TEST: Form Request should reject include parameters (not supported)
        Tag::factory()->count(3)->create();

        $response = $this->getJson(route('tag.index', [
            'include' => 'items,translations',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'include',
            ],
        ]);
    }

    public function test_index_rejects_invalid_include_parameters()
    {
        Tag::factory()->count(2)->create();

        $response = $this->getJson(route('tag.index', [
            'include' => 'invalid_relation,fake_includes,nonexistent',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['include']);
    }

    public function test_index_rejects_unexpected_query_parameters()
    {
        // SECURITY TEST: Form Request should reject unexpected parameters
        Tag::factory()->count(2)->create();

        $response = $this->getJson(route('tag.index', [
            'page' => 1,
            'include' => 'items',
            'category' => 'colors', // Not implemented
            'popularity' => 'high', // Not implemented
            'search_term' => 'blue', // Not implemented
            'filter_active' => true, // Not implemented
            'admin_debug' => true,
            'export_tags' => 'csv',
            'sensitive_operation' => 'delete_all',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'include',
                'category',
                'popularity',
                'search_term',
                'filter_active',
                'admin_debug',
                'export_tags',
                'sensitive_operation',
            ],
        ]);
    }

    // SHOW ENDPOINT TESTS
    public function test_show_accepts_no_query_parameters()
    {
        $tag = Tag::factory()->create();

        $response = $this->getJson(route('tag.show', $tag));

        $response->assertOk();
    }

    public function test_show_rejects_include_parameters()
    {
        // SECURITY TEST: Form Request should reject include parameters (not supported)
        $tag = Tag::factory()->create();

        $response = $this->getJson(route('tag.show', $tag).'?include=items,translations');

        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'include',
            ],
        ]);
    }

    public function test_show_rejects_unexpected_query_parameters()
    {
        // SECURITY TEST: Form Request should reject unexpected parameters
        $tag = Tag::factory()->create();

        $response = $this->getJson(route('tag.show', $tag).'?include=items&detailed_view=true&admin_mode=1&show_usage_stats=true');

        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'include',
                'detailed_view',
                'admin_mode',
                'show_usage_stats',
            ],
        ]);
    }

    // STORE ENDPOINT TESTS
    public function test_store_validates_required_fields()
    {
        $response = $this->postJson(route('tag.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name', 'description']);
    }

    public function test_store_validates_unique_internal_name()
    {
        $existingTag = Tag::factory()->create();

        $response = $this->postJson(route('tag.store'), [
            'internal_name' => $existingTag->internal_name,
            'description' => 'Test description',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_store_prohibits_id_field()
    {
        $response = $this->postJson(route('tag.store'), [
            'id' => 'some-uuid', // Should be prohibited
            'internal_name' => 'Test Tag',
            'description' => 'Test description',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_store_accepts_valid_data()
    {
        $response = $this->postJson(route('tag.store'), [
            'internal_name' => 'Modern Art',
            'description' => 'Modern art collection tag',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.internal_name', 'Modern Art');
        $response->assertJsonPath('data.description', 'Modern art collection tag');
    }

    public function test_store_accepts_optional_backward_compatibility_field()
    {
        $response = $this->postJson(route('tag.store'), [
            'internal_name' => 'Legacy Tag',
            'description' => 'Legacy tag description',
            'backward_compatibility' => 'old_tag_789',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.backward_compatibility', 'old_tag_789');
    }

    public function test_store_rejects_unexpected_request_parameters()
    {
        // SECURITY TEST: Form Request should reject unexpected parameters
        $response = $this->postJson(route('tag.store'), [
            'internal_name' => 'Test Tag',
            'description' => 'Test description',
            'unexpected_field' => 'should_be_rejected',
            'color' => '#FF0000', // Not implemented
            'icon' => 'fas fa-tag', // Not implemented
            'admin_created' => true,
            'popularity_score' => 100,
            'debug_mode' => true,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'unexpected_field',
                'color',
                'icon',
                'admin_created',
                'popularity_score',
                'debug_mode',
            ],
        ]);
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_validates_unique_internal_name()
    {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $response = $this->putJson(route('tag.update', $tag1), [
            'internal_name' => $tag2->internal_name,
            'description' => 'Updated description',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_update_prohibits_id_modification()
    {
        $tag = Tag::factory()->create();

        $response = $this->putJson(route('tag.update', $tag), [
            'id' => 'new-uuid', // Should be prohibited
            'internal_name' => 'Updated Tag',
            'description' => 'Updated description',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_update_accepts_same_internal_name()
    {
        $tag = Tag::factory()->create();

        $response = $this->putJson(route('tag.update', $tag), [
            'internal_name' => $tag->internal_name, // Same name should be allowed
            'description' => 'Updated description',
        ]);

        $response->assertOk();
    }

    public function test_update_rejects_unexpected_request_parameters()
    {
        // SECURITY TEST: Form Request should reject unexpected parameters
        $tag = Tag::factory()->create();

        $response = $this->putJson(route('tag.update', $tag), [
            'internal_name' => 'Updated Tag',
            'description' => 'Updated description',
            'unexpected_field' => 'should_be_rejected',
            'change_category' => 'featured',
            'boost_popularity' => true,
            'assign_curator' => 'admin_user',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'unexpected_field',
                'change_category',
                'boost_popularity',
                'assign_curator',
            ],
        ]);
    }

    // SPECIAL ENDPOINT TESTS - Tag has special routes
    public function test_for_item_endpoint_rejects_unexpected_query_parameters()
    {
        // SECURITY TEST: Form Request should reject unexpected parameters
        $item = \App\Models\Item::factory()->create();

        $response = $this->getJson(route('tag.forItem', $item).'?include=translations&admin_view=true&debug=verbose');

        $response->assertUnprocessable();
        $response->assertJsonStructure([
            'message',
            'errors' => [
                'include',
                'admin_view',
                'debug',
            ],
        ]);
    }

    // EDGE CASE TESTS
    public function test_handles_unicode_characters_in_internal_name()
    {
        $unicodeNames = [
            'Tag ñ España',
            'Étiquette française',
            'Тег кириллица',
            'タグ日本語',
            'علامة عربية',
            'Ετικέτα ελληνικά',
            'Tag italiano',
        ];

        foreach ($unicodeNames as $name) {
            $response = $this->postJson(route('tag.store'), [
                'internal_name' => $name,
                'description' => "Description for {$name}",
            ]);

            $response->assertCreated(); // Should handle Unicode gracefully
        }
    }

    public function test_handles_special_characters_in_internal_name()
    {
        $specialCharNames = [
            'Tag "With Quotes"',
            "Tag 'With Apostrophes'",
            'Tag & Ampersand',
            'Tag <With> Brackets',
            'Tag @ Symbol',
            'Tag # Hash',
            'Tag % Percent',
            'Tag $ Dollar',
            'Tag * Asterisk',
            'Tag + Plus',
            'Tag = Equals',
            'Tag | Pipe',
            'Tag \\ Backslash',
            'Tag / Forward Slash',
            'Tag : Colon',
            'Tag ; Semicolon',
            'Tag ? Question',
            'Tag ! Exclamation',
        ];

        foreach ($specialCharNames as $name) {
            $response = $this->postJson(route('tag.store'), [
                'internal_name' => $name,
                'description' => "Description for {$name}",
            ]);

            // Should handle gracefully
            $this->assertContains($response->status(), [201, 422]);
        }
    }

    public function test_handles_very_long_internal_name()
    {
        $veryLongName = str_repeat('Very Long Tag Name ', 200); // Very long string

        $response = $this->postJson(route('tag.store'), [
            'internal_name' => $veryLongName,
            'description' => 'Description for very long tag name',
        ]);

        // Should handle gracefully - either accept (if no length limit) or reject with validation
        $this->assertContains($response->status(), [201, 422]);
    }

    public function test_handles_whitespace_variations_in_internal_name()
    {
        $whitespaceVariations = [
            '   Tag with leading spaces',
            'Tag with trailing spaces   ',
            'Tag  with  double  spaces',
            "Tag\twith\ttabs",
            "Tag\nwith\nnewlines",
            " \t Tag \n with \r mixed \t whitespace \n ",
        ];

        foreach ($whitespaceVariations as $name) {
            $response = $this->postJson(route('tag.store'), [
                'internal_name' => $name,
            ]);

            // Should handle gracefully
            $this->assertContains($response->status(), [201, 422]);
        }
    }

    public function test_handles_empty_and_whitespace_only_internal_name()
    {
        $emptyNames = [
            '', // Completely empty
            '   ', // Spaces only
            "\t\t", // Tabs only
            "\n\n", // Newlines only
            " \t \n \r ", // Mixed whitespace only
        ];

        foreach ($emptyNames as $name) {
            $response = $this->postJson(route('tag.store'), [
                'internal_name' => $name,
            ]);

            $response->assertUnprocessable(); // Should reject empty names
            $response->assertJsonValidationErrors(['internal_name']);
        }
    }

    public function test_handles_numeric_only_internal_name()
    {
        $numericNames = [
            '123',
            '0',
            '999999',
            '3.14159',
            '-42',
            '1e10',
        ];

        foreach ($numericNames as $name) {
            $response = $this->postJson(route('tag.store'), [
                'internal_name' => $name,
                'description' => "Description for numeric tag {$name}",
            ]);

            $response->assertCreated(); // Numeric names should be allowed
        }
    }

    public function test_handles_malformed_json_gracefully()
    {
        $response = $this->postJson(route('tag.store'), [
            'internal_name' => ['array' => 'instead_of_string'], // Wrong type
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_pagination_boundary_conditions()
    {
        Tag::factory()->count(20)->create();

        // Test boundary conditions
        $validCases = [
            ['per_page' => 1], // Minimum
            ['per_page' => 100], // Maximum
            ['page' => 1], // First page
        ];

        foreach ($validCases as $params) {
            $response = $this->getJson(route('tag.index', $params));
            $response->assertOk();
        }

        // Test invalid conditions
        $invalidCases = [
            ['per_page' => 0], // Below minimum
            ['per_page' => 101], // Above maximum
            ['per_page' => -1], // Negative
            ['page' => 0], // Below minimum
            ['page' => -1], // Negative
            ['per_page' => 'abc'], // Invalid type
            ['page' => 'xyz'], // Invalid type
        ];

        foreach ($invalidCases as $params) {
            $response = $this->getJson(route('tag.index', $params));
            $response->assertUnprocessable();
        }
    }
}

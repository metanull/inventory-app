<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Theme;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive parameter validation tests for Theme API endpoints
 */
class ThemeParameterValidationTest extends TestCase
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
        Theme::factory()->count(25)->create();

        $response = $this->getJson(route('theme.index', [
            'page' => 3,
            'per_page' => 8,
        ]));

        $response->assertOk();
        $response->assertJsonPath('meta.current_page', 3);
        $response->assertJsonPath('meta.per_page', 8);
    }

    public function test_index_accepts_valid_include_parameters()
    {
        Theme::factory()->count(5)->create();

        $response = $this->getJson(route('theme.index', [
            'include' => 'translations,subthemes',
        ]));

        $response->assertOk();
    }

    public function test_index_rejects_invalid_include_parameters()
    {
        Theme::factory()->count(3)->create();

        $response = $this->getJson(route('theme.index', [
            'include' => 'invalid_relation,fake_themes,non_existent',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['include']);
    }

    public function test_index_rejects_unexpected_query_parameters_securely()
    {
        // SECURITY TEST: Form Request should reject unexpected parameters
        Theme::factory()->count(3)->create();

        $response = $this->getJson(route('theme.index', [
            'page' => 1,
            'include' => 'translations',
            'category' => 'art', // Not implemented
            'time_period' => 'renaissance', // Not implemented
            'popularity' => 'high', // Not implemented
            'classification' => 'cultural', // Not implemented
            'admin_access' => true,
            'debug_themes' => true,
            'export_format' => 'csv',
            'bulk_operation' => 'merge_all',
        ]));

        $response->assertStatus(422); // Form Request now correctly rejects unexpected params
        $response->assertJsonValidationErrors(['category', 'time_period', 'popularity']);
    }

    // SHOW ENDPOINT TESTS
    public function test_show_accepts_valid_include_parameters()
    {
        $theme = Theme::factory()->create();

        $response = $this->getJson(route('theme.show', $theme).'?include=translations,subthemes');

        $response->assertOk();
    }

    public function test_show_rejects_unexpected_query_parameters_securely()
    {
        // SECURITY TEST: Form Request should reject unexpected parameters
        $theme = Theme::factory()->create();

        $response = $this->getJson(route('theme.show', $theme).'?include=translations&show_statistics=true&usage_details=full');

        $response->assertStatus(422); // Form Request now correctly rejects unexpected params
        $response->assertJsonValidationErrors(['show_statistics', 'usage_details']);
    }

    // STORE ENDPOINT TESTS
    public function test_store_validates_required_fields()
    {
        $response = $this->postJson(route('theme.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_store_validates_unique_internal_name()
    {
        $exhibition = \App\Models\Exhibition::factory()->create();
        $existingTheme = Theme::factory()->create(['exhibition_id' => $exhibition->id]);

        $response = $this->postJson(route('theme.store'), [
            'internal_name' => $existingTheme->internal_name,
            'exhibition_id' => $exhibition->id, // Same exhibition = should trigger unique constraint
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_store_prohibits_id_field()
    {
        $response = $this->postJson(route('theme.store'), [
            'id' => 'some-uuid', // Should be prohibited
            'internal_name' => 'Test Theme',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_store_accepts_valid_data()
    {
        $exhibition = \App\Models\Exhibition::factory()->create();

        $response = $this->postJson(route('theme.store'), [
            'internal_name' => 'Renaissance Art',
            'exhibition_id' => $exhibition->id,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.internal_name', 'Renaissance Art');
    }

    public function test_store_accepts_optional_backward_compatibility_field()
    {
        $exhibition = \App\Models\Exhibition::factory()->create();

        $response = $this->postJson(route('theme.store'), [
            'internal_name' => 'Historical Theme',
            'exhibition_id' => $exhibition->id,
            'backward_compatibility' => 'old_theme_456',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.backward_compatibility', 'old_theme_456');
    }

    public function test_store_rejects_unexpected_request_parameters_securely()
    {
        // SECURITY TEST: Form Request should reject unexpected parameters
        $response = $this->postJson(route('theme.store'), [
            'internal_name' => 'Test Theme',
            'unexpected_field' => 'should_be_rejected',
            'category' => 'Fine Arts', // Not implemented
            'era' => 'Modern', // Not implemented
            'geographical_scope' => 'European', // Not implemented
            'complexity_level' => 'advanced', // Not implemented
            'admin_created' => true,
            'malicious_script' => '<script>alert("xss")</script>',
            'sql_injection' => "'; DROP TABLE themes; --",
            'privilege_escalation' => 'admin_access',
        ]);

        $response->assertStatus(422); // Form Request now correctly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field', 'category', 'era']);
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_validates_unique_internal_name()
    {
        $theme1 = Theme::factory()->create();
        $theme2 = Theme::factory()->create();

        $response = $this->putJson(route('theme.update', $theme1), [
            'internal_name' => $theme2->internal_name,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_update_prohibits_id_modification()
    {
        $theme = Theme::factory()->create();

        $response = $this->putJson(route('theme.update', $theme), [
            'id' => 'new-uuid', // Should be prohibited
            'internal_name' => 'Updated Theme',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_update_accepts_same_internal_name()
    {
        $theme = Theme::factory()->create();

        $response = $this->putJson(route('theme.update', $theme), [
            'internal_name' => $theme->internal_name, // Same name should be allowed
        ]);

        $response->assertOk();
    }

    public function test_update_rejects_unexpected_request_parameters_securely()
    {
        // SECURITY TEST: Form Request should reject unexpected parameters
        $theme = Theme::factory()->create();

        $response = $this->putJson(route('theme.update', $theme), [
            'internal_name' => 'Updated Theme',
            'unexpected_field' => 'should_be_rejected',
            'change_category' => 'modern_art',
            'update_scope' => 'global',
            'merge_with' => 'another_theme_id',
        ]);

        $response->assertStatus(422); // Form Request now correctly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field', 'change_category', 'update_scope']);
    }

    // EDGE CASE TESTS
    public function test_handles_unicode_characters_in_internal_name()
    {
        $unicodeNames = [
            'Art français',
            'Искусство русское',
            '芸術日本語',
            'فن عربي',
            'Arte español',
            'Arte italiano',
            'Sztuka polska',
            'Τέχνη ελληνική',
            'Kunst dansk',
            'Művészet magyar',
        ];

        foreach ($unicodeNames as $name) {
            $exhibition = \App\Models\Exhibition::factory()->create();
            $response = $this->postJson(route('theme.store'), [
                'internal_name' => $name,
                'exhibition_id' => $exhibition->id,
            ]);

            $response->assertCreated(); // Should handle Unicode gracefully
        }
    }

    public function test_handles_special_characters_in_internal_name()
    {
        $specialCharNames = [
            'Theme "Modern Art"',
            "Theme 'Contemporary'",
            'Theme & Culture',
            'Theme: Renaissance',
            'Theme (Ancient)',
            'Theme - Medieval',
            'Theme @ Gallery',
            'Theme #1: Masters',
            'Theme 50% Art',
            'Theme $ Value',
            'Theme * Featured',
            'Theme + New',
            'Theme = Excellence',
            'Theme | Curated',
        ];

        foreach ($specialCharNames as $name) {
            $response = $this->postJson(route('theme.store'), [
                'internal_name' => $name,
            ]);

            // Should handle gracefully
            $this->assertContains($response->status(), [201, 422]);
        }
    }

    public function test_handles_very_long_internal_name()
    {
        $veryLongName = str_repeat('Very Long Theme Name With Detailed Historical Context And Cultural Significance ', 20);

        $response = $this->postJson(route('theme.store'), [
            'internal_name' => $veryLongName,
        ]);

        // Should handle gracefully
        $this->assertContains($response->status(), [201, 422]);
    }

    public function test_handles_empty_and_whitespace_internal_names()
    {
        $emptyNames = [
            '', // Empty
            '   ', // Spaces only
            "\t\t", // Tabs only
            "\n\n", // Newlines only
            "  \t\n  ", // Mixed whitespace
        ];

        foreach ($emptyNames as $name) {
            $response = $this->postJson(route('theme.store'), [
                'internal_name' => $name,
            ]);

            $response->assertUnprocessable(); // Should reject empty names
            $response->assertJsonValidationErrors(['internal_name']);
        }
    }

    public function test_handles_null_vs_empty_string_for_optional_fields()
    {
        $theme = Theme::factory()->create();

        $testCases = [
            ['backward_compatibility' => null],
            ['backward_compatibility' => ''],
        ];

        foreach ($testCases as $data) {
            $updateData = array_merge([
                'internal_name' => 'Test Theme Update',
            ], $data);

            $response = $this->putJson(route('theme.update', $theme), $updateData);

            // Should handle gracefully
            $this->assertContains($response->status(), [200, 422]);
        }
    }

    public function test_pagination_with_many_themes()
    {
        Theme::factory()->count(70)->create();

        $testCases = [
            ['page' => 1, 'per_page' => 15],
            ['page' => 2, 'per_page' => 25],
            ['page' => 1, 'per_page' => 100], // Maximum
        ];

        foreach ($testCases as $params) {
            $response = $this->getJson(route('theme.index', $params));
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
            $response = $this->getJson(route('theme.index', $params));
            $response->assertUnprocessable();
        }
    }

    public function test_handles_array_injection_attempts()
    {
        $response = $this->postJson(route('theme.store'), [
            'internal_name' => ['array' => 'instead_of_string'],
            'backward_compatibility' => ['malicious' => 'array'],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['internal_name']);
    }

    public function test_handles_theme_name_variations()
    {
        $themeNameVariations = [
            'Renaissance Art & Culture',
            'Ancient Civilizations',
            'Modern Architecture',
            'Traditional Crafts',
            'Contemporary Photography',
            'Historical Portraits',
            'Religious Art',
            'Folk Music & Dance',
            'Scientific Instruments',
            'Maritime Heritage',
            'War & Peace',
            'Industrial Revolution',
            'Colonial Period',
            'Digital Age',
            'Cultural Exchange',
            'Social Movements',
            'Environmental Art',
            'Gender & Identity',
            'Migration Stories',
            'Technological Innovation',
        ];

        foreach ($themeNameVariations as $name) {
            $exhibition = \App\Models\Exhibition::factory()->create();
            $response = $this->postJson(route('theme.store'), [
                'internal_name' => $name,
                'exhibition_id' => $exhibition->id,
            ]);

            $response->assertCreated(); // Should handle various theme name formats
        }
    }

    public function test_handles_case_sensitivity_for_internal_name_uniqueness()
    {
        Theme::factory()->create(['internal_name' => 'Renaissance Art']);

        $caseSensitivityTests = [
            'renaissance art', // lowercase
            'RENAISSANCE ART', // uppercase
            'Renaissance art', // different case
            'renaissance ART', // mixed case
        ];

        foreach ($caseSensitivityTests as $name) {
            $response = $this->postJson(route('theme.store'), [
                'internal_name' => $name,
            ]);

            // Depending on database collation, these might be unique or not
            $this->assertContains($response->status(), [201, 422]);
        }
    }

    public function test_handles_theme_name_with_numbers_and_versions()
    {
        $numberedThemeNames = [
            'Theme v1.0',
            'Theme 2024',
            'Theme #1',
            'Theme 001',
            '20th Century Art',
            'Theme Beta',
            'Theme Alpha',
            'Phase 1 Theme',
            'Generation 2',
            'Version 3.14',
        ];

        foreach ($numberedThemeNames as $name) {
            $exhibition = \App\Models\Exhibition::factory()->create();
            $response = $this->postJson(route('theme.store'), [
                'internal_name' => $name,
                'exhibition_id' => $exhibition->id,
            ]);

            $response->assertCreated(); // Should handle numbered theme names
        }
    }

    public function test_handles_theme_name_with_dates_and_periods()
    {
        $dateBasedThemeNames = [
            '15th Century Art',
            '1900-1950 Period',
            'Post-War Era',
            'Pre-Columbian',
            'Medieval Times',
            'Bronze Age',
            'Stone Age',
            'Baroque Period',
            'Victorian Era',
            'Modern Era (1900-2000)',
        ];

        foreach ($dateBasedThemeNames as $name) {
            $exhibition = \App\Models\Exhibition::factory()->create();
            $response = $this->postJson(route('theme.store'), [
                'internal_name' => $name,
                'exhibition_id' => $exhibition->id,
            ]);

            $response->assertCreated(); // Should handle date-based theme names
        }
    }

    public function test_handles_geographical_theme_names()
    {
        $geographicalThemeNames = [
            'European Art',
            'Asian Culture',
            'African Heritage',
            'American History',
            'Mediterranean Art',
            'Nordic Traditions',
            'Latin American Culture',
            'Middle Eastern Art',
            'Pacific Islands',
            'Arctic Heritage',
        ];

        foreach ($geographicalThemeNames as $name) {
            $exhibition = \App\Models\Exhibition::factory()->create();
            $response = $this->postJson(route('theme.store'), [
                'internal_name' => $name,
                'exhibition_id' => $exhibition->id,
            ]);

            $response->assertCreated(); // Should handle geographical theme names
        }
    }
}

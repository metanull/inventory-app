<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Language;
use App\Models\Theme;
use App\Models\ThemeTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive parameter validation tests for ThemeTranslation API endpoints
 */
class ThemeTranslationParameterValidationTest extends TestCase
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
        $theme = Theme::factory()->create();
        ThemeTranslation::factory()->count(12)->create(['theme_id' => $theme->id]);

        $response = $this->getJson(route('theme-translation.index', [
            'page' => 2,
            'per_page' => 6,
        ]));

        $response->assertOk();
        $response->assertJsonPath('meta.current_page', 2);
        $response->assertJsonPath('meta.per_page', 6);
    }

    public function test_index_accepts_valid_include_parameters()
    {
        $theme = Theme::factory()->create();
        ThemeTranslation::factory()->count(3)->create(['theme_id' => $theme->id]);

        $response = $this->getJson(route('theme-translation.index', [
            'include' => 'theme,language',
        ]));

        $response->assertOk();
    }

    public function test_index_rejects_invalid_include_parameters()
    {
        $theme = Theme::factory()->create();
        ThemeTranslation::factory()->count(2)->create(['theme_id' => $theme->id]);

        $response = $this->getJson(route('theme-translation.index', [
            'include' => 'invalid_relation,fake_theme,non_existent',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['include']);
    }

    public function test_index_rejects_unexpected_query_parameters_currently()
    {
        $theme = Theme::factory()->create();
        ThemeTranslation::factory()->count(2)->create(['theme_id' => $theme->id]);

        $response = $this->getJson(route('theme-translation.index', [
            'page' => 1,
            'include' => 'theme',
            'filter_by_category' => 'historical', // Not implemented
            'sort_by_popularity' => 'desc', // Not implemented
            'content_type' => 'academic', // Not implemented
            'admin_access' => true,
            'debug_translations' => true,
            'export_format' => 'xml',
            'bulk_operation' => 'analyze_all',
        ]));

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['filter_by_category']);
    }

    // SHOW ENDPOINT TESTS
    public function test_show_accepts_valid_include_parameters()
    {
        $theme = Theme::factory()->create();
        $translation = ThemeTranslation::factory()->create(['theme_id' => $theme->id]);

        $response = $this->getJson(route('theme-translation.show', $translation).'?include=theme,language');

        $response->assertOk();
    }

    public function test_show_rejects_unexpected_query_parameters_currently()
    {
        $theme = Theme::factory()->create();
        $translation = ThemeTranslation::factory()->create(['theme_id' => $theme->id]);

        $response = $this->getJson(route('theme-translation.show', $translation).'?include=theme&show_metadata=true&content_analysis=full');

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['show_metadata']);
    }

    // STORE ENDPOINT TESTS
    public function test_store_validates_required_fields()
    {
        $response = $this->postJson(route('theme-translation.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['theme_id', 'language_code']);
    }

    public function test_store_validates_theme_id_exists()
    {
        $language = Language::factory()->create();

        $response = $this->postJson(route('theme-translation.store'), [
            'theme_id' => 'non-existent-uuid',
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['theme_id']);
    }

    public function test_store_validates_language_code_exists()
    {
        $theme = Theme::factory()->create();

        $response = $this->postJson(route('theme-translation.store'), [
            'theme_id' => $theme->id,
            'language_code' => 'xyz', // Invalid language code
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_code']);
    }

    public function test_store_validates_unique_combination()
    {
        $theme = Theme::factory()->create();
        $language = Language::factory()->create();
        ThemeTranslation::factory()->create([
            'theme_id' => $theme->id,
            'language_code' => $language->code,
        ]);

        $response = $this->postJson(route('theme-translation.store'), [
            'theme_id' => $theme->id,
            'language_code' => $language->code, // Duplicate combination
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['theme_id']);
    }

    public function test_store_validates_theme_id_uuid_format()
    {
        $language = Language::factory()->create();

        $response = $this->postJson(route('theme-translation.store'), [
            'theme_id' => 'not-a-uuid',
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['theme_id']);
    }

    public function test_store_validates_language_code_format()
    {
        $theme = Theme::factory()->create();

        $response = $this->postJson(route('theme-translation.store'), [
            'theme_id' => $theme->id,
            'language_code' => 'toolong', // Should be 3 characters
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_code']);
    }

    public function test_store_prohibits_id_field()
    {
        $theme = Theme::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('theme-translation.store'), [
            'id' => 'some-uuid', // Should be prohibited
            'theme_id' => $theme->id,
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_store_accepts_valid_data()
    {
        $theme = Theme::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('theme-translation.store'), [
            'theme_id' => $theme->id,
            'language_code' => $language->code,
            'title' => 'Translated Theme Title',
            'description' => 'Translated theme description',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.theme_id', $theme->id);
        $response->assertJsonPath('data.language_code', $language->code);
    }

    public function test_store_rejects_unexpected_request_parameters_currently()
    {
        $theme = Theme::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('theme-translation.store'), [
            'theme_id' => $theme->id,
            'language_code' => $language->code,
            'title' => 'Test Theme Title',
            'description' => 'Test theme description',
            'unexpected_field' => 'should_be_rejected',
            'keywords' => 'history, culture, heritage', // Not implemented
            'target_audience' => 'academic', // Not implemented
            'content_level' => 'advanced', // Not implemented
            'related_themes' => 'theme1,theme2', // Not implemented
            'admin_created' => true,
            'malicious_script' => '<script>alert("XSS")</script>',
            'sql_injection' => "'; DROP TABLE theme_translations; --",
            'privilege_escalation' => 'theme_admin',
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_validates_unique_combination_on_change()
    {
        $theme1 = Theme::factory()->create();
        $theme2 = Theme::factory()->create();
        $language = Language::factory()->create();

        $translation1 = ThemeTranslation::factory()->create([
            'theme_id' => $theme1->id,
            'language_code' => $language->code,
        ]);

        ThemeTranslation::factory()->create([
            'theme_id' => $theme2->id,
            'language_code' => $language->code,
        ]);

        $response = $this->putJson(route('theme-translation.update', $translation1), [
            'theme_id' => $theme2->id, // Would create duplicate
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['theme_id']);
    }

    public function test_update_prohibits_id_modification()
    {
        $theme = Theme::factory()->create();
        $translation = ThemeTranslation::factory()->create(['theme_id' => $theme->id]);

        $response = $this->putJson(route('theme-translation.update', $translation), [
            'id' => 'new-uuid', // Should be prohibited
            'theme_id' => $theme->id,
            'language_code' => $translation->language_code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_update_accepts_same_combination()
    {
        $theme = Theme::factory()->create();
        $translation = ThemeTranslation::factory()->create(['theme_id' => $theme->id]);

        $response = $this->putJson(route('theme-translation.update', $translation), [
            'theme_id' => $translation->theme_id, // Same combination
            'language_code' => $translation->language_code,
            'title' => 'Updated theme title',
        ]);

        $response->assertOk();
    }

    public function test_update_rejects_unexpected_request_parameters_currently()
    {
        $theme = Theme::factory()->create();
        $translation = ThemeTranslation::factory()->create(['theme_id' => $theme->id]);

        $response = $this->putJson(route('theme-translation.update', $translation), [
            'theme_id' => $translation->theme_id,
            'language_code' => $translation->language_code,
            'title' => 'Updated Theme Translation',
            'unexpected_field' => 'should_be_rejected',
            'change_status' => 'reviewed',
            'update_priority' => 'urgent',
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // EDGE CASE TESTS
    public function test_handles_unicode_characters_in_translation_fields()
    {
        $theme = Theme::factory()->create();

        $unicodeTitles = [
            'Thème français de patrimoine culturel',
            'Русская тема культурного наследия',
            '日本の文化遺産テーマ',
            'موضوع التراث الثقافي العربي',
            'Tema español de patrimonio cultural',
            'Tema italiano del patrimonio culturale',
            'Temat polski dziedzictwa kulturowego',
            'Ελληνικό θέμα πολιτιστικής κληρονομιάς',
            'Dansk tema for kulturarv',
            'Magyar kulturális örökség téma',
        ];

        foreach ($unicodeTitles as $index => $title) {
            $newLanguage = Language::factory()->create(['code' => sprintf('%03d', $index)]);

            $response = $this->postJson(route('theme-translation.store'), [
                'theme_id' => $theme->id,
                'language_code' => $newLanguage->code,
                'title' => $title,
                'description' => "Description for {$title}",
            ]);

            $response->assertCreated(); // Should handle Unicode gracefully
        }
    }

    public function test_handles_empty_and_null_optional_fields()
    {
        $theme = Theme::factory()->create();
        $translation = ThemeTranslation::factory()->create(['theme_id' => $theme->id]);

        $testCases = [
            ['title' => null, 'description' => null],
            ['title' => '', 'description' => ''],
            ['title' => '   ', 'description' => '   '], // Whitespace only
            ['title' => 'Valid Title', 'description' => null],
            ['title' => null, 'description' => 'Valid Description'],
        ];

        foreach ($testCases as $data) {
            $updateData = array_merge([
                'theme_id' => $translation->theme_id,
                'language_code' => $translation->language_code,
            ], $data);

            $response = $this->putJson(route('theme-translation.update', $translation), $updateData);

            // Should handle gracefully
            $this->assertContains($response->status(), [200, 422]);
        }
    }

    public function test_handles_very_long_translation_content()
    {
        $theme = Theme::factory()->create();
        $language = Language::factory()->create();

        $veryLongTitle = str_repeat('Very Long Theme Title With Extended Cultural Analysis ', 8);
        $veryLongDescription = str_repeat('Very Long Theme Description With Comprehensive Historical Context And Detailed Academic Analysis ', 12);

        $response = $this->postJson(route('theme-translation.store'), [
            'theme_id' => $theme->id,
            'language_code' => $language->code,
            'title' => $veryLongTitle,
            'description' => $veryLongDescription,
        ]);

        // Should handle gracefully
        $this->assertContains($response->status(), [201, 422]);
    }

    public function test_handles_array_injection_attempts()
    {
        $theme = Theme::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('theme-translation.store'), [
            'theme_id' => ['array' => 'instead_of_uuid'],
            'language_code' => ['malicious' => 'array'],
            'title' => ['injection' => 'attempt'],
            'description' => ['another' => 'injection'],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['theme_id']);
    }

    public function test_pagination_with_many_translations()
    {
        $theme = Theme::factory()->create();
        ThemeTranslation::factory()->count(48)->create(['theme_id' => $theme->id]);

        $testCases = [
            ['page' => 1, 'per_page' => 16],
            ['page' => 2, 'per_page' => 18],
            ['page' => 1, 'per_page' => 100], // Maximum
        ];

        foreach ($testCases as $params) {
            $response = $this->getJson(route('theme-translation.index', $params));
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
            $response = $this->getJson(route('theme-translation.index', $params));
            $response->assertUnprocessable();
        }
    }

    public function test_handles_special_characters_in_translation_content()
    {
        $theme = Theme::factory()->create();

        $specialCharTitles = [
            'Theme "With Quotes" Here',
            "Theme 'With Apostrophes' Content",
            'Theme & Symbol Content',
            'Theme: Colon Content',
            'Theme (Parentheses) Content',
            'Theme - Dash Content',
            'Theme @ Symbol Content',
            'Theme #Hashtag Content',
            'Theme 50% Percentage',
            'Theme $Dollar Content',
            'Theme *Asterisk Content',
            'Theme +Plus Content',
            'Theme =Equals Content',
            'Theme |Pipe Content',
        ];

        foreach ($specialCharTitles as $index => $title) {
            $newLanguage = Language::factory()->create(['code' => sprintf('s%02d', $index)]);

            $response = $this->postJson(route('theme-translation.store'), [
                'theme_id' => $theme->id,
                'language_code' => $newLanguage->code,
                'title' => $title,
                'description' => "Description for {$title}",
            ]);

            // Should handle gracefully
            $this->assertContains($response->status(), [201, 422]);
        }
    }

    public function test_handles_theme_translation_workflow()
    {
        $theme = Theme::factory()->create();
        $english = Language::factory()->create(['code' => 'eng']);
        $french = Language::factory()->create(['code' => 'fra']);
        $spanish = Language::factory()->create(['code' => 'spa']);

        // Create translations in different languages
        $languages = [$english, $french, $spanish];
        $titles = [
            'Cultural Heritage and Identity',
            'Patrimoine Culturel et Identité',
            'Patrimonio Cultural e Identidad',
        ];
        $descriptions = [
            'Exploring the complex relationships between cultural heritage preservation and community identity...',
            'Explorer les relations complexes entre la préservation du patrimoine culturel et l\'identité communautaire...',
            'Explorando las relaciones complejas entre la preservación del patrimonio cultural y la identidad comunitaria...',
        ];

        foreach ($languages as $index => $language) {
            $response = $this->postJson(route('theme-translation.store'), [
                'theme_id' => $theme->id,
                'language_code' => $language->code,
                'title' => $titles[$index],
                'description' => $descriptions[$index],
            ]);

            $response->assertCreated();
        }

        // Verify all translations exist
        $indexResponse = $this->getJson(route('theme-translation.index'));
        $indexResponse->assertOk();
        $indexResponse->assertJsonPath('meta.total', 3);
    }

    public function test_handles_academic_theme_variations()
    {
        $theme = Theme::factory()->create();

        $academicThemes = [
            'Archaeological Methodology and Field Techniques',
            'Conservation Science and Materials Analysis',
            'Digital Humanities and Virtual Reconstruction',
            'Ethnographic Studies and Cultural Documentation',
            'Art Historical Interpretation and Stylistic Analysis',
            'Museum Studies and Curatorial Practice',
            'Heritage Tourism and Community Engagement',
            'Interdisciplinary Research in Cultural Studies',
            'Post-Colonial Perspectives in Museum Practice',
            'Environmental Archaeology and Climate Change',
            'Oral History and Intangible Heritage',
            'Technology Integration in Cultural Preservation',
            'Social Justice and Inclusive Museum Practices',
            'Comparative Cultural Analysis and Global Perspectives',
            'Educational Outreach and Public Archaeology',
        ];

        foreach ($academicThemes as $index => $title) {
            $newLanguage = Language::factory()->create(['code' => sprintf('a%02d', $index)]);

            $response = $this->postJson(route('theme-translation.store'), [
                'theme_id' => $theme->id,
                'language_code' => $newLanguage->code,
                'title' => $title,
                'description' => "Academic exploration of {$title} with theoretical frameworks and practical applications.",
            ]);

            $response->assertCreated(); // Should handle academic theme variations
        }
    }

    public function test_handles_cultural_theme_variations()
    {
        $theme = Theme::factory()->create();

        $culturalThemes = [
            'Indigenous Knowledge Systems and Traditional Practices',
            'Migration Patterns and Cultural Exchange',
            'Religious Symbolism and Spiritual Traditions',
            'Folk Art and Vernacular Expression',
            'Craft Traditions and Artisan Communities',
            'Storytelling and Oral Literature',
            'Festivals and Ceremonial Practices',
            'Food Culture and Culinary Heritage',
            'Music and Dance Traditions',
            'Textile Arts and Weaving Traditions',
            'Architecture and Settlement Patterns',
            'Trade Networks and Economic Systems',
            'Social Hierarchies and Power Structures',
            'Gender Roles and Family Structures',
            'Environmental Adaptation and Resource Management',
        ];

        foreach ($culturalThemes as $index => $title) {
            $newLanguage = Language::factory()->create(['code' => sprintf('c%02d', $index)]);

            $response = $this->postJson(route('theme-translation.store'), [
                'theme_id' => $theme->id,
                'language_code' => $newLanguage->code,
                'title' => $title,
                'description' => "Cultural examination of {$title} within historical and contemporary contexts.",
            ]);

            $response->assertCreated(); // Should handle cultural theme variations
        }
    }

    public function test_handles_research_theme_variations()
    {
        $theme = Theme::factory()->create();

        $researchThemes = [
            'Provenance Research and Ownership History',
            'Scientific Analysis and Authentication Methods',
            'Comparative Studies and Cross-Cultural Analysis',
            'Temporal Dynamics and Chronological Frameworks',
            'Spatial Analysis and Geographic Distribution',
            'Material Culture Studies and Object Biography',
            'Digital Documentation and 3D Modeling',
            'Community-Based Participatory Research',
            'Collaborative Knowledge Production',
            'Decolonizing Research Methodologies',
            'Ethical Considerations in Cultural Research',
            'Open Access and Knowledge Sharing',
            'Interdisciplinary Collaboration Models',
            'Grant Writing and Funding Strategies',
            'Publication and Dissemination Practices',
        ];

        foreach ($researchThemes as $index => $title) {
            $newLanguage = Language::factory()->create(['code' => sprintf('r%02d', $index)]);

            $response = $this->postJson(route('theme-translation.store'), [
                'theme_id' => $theme->id,
                'language_code' => $newLanguage->code,
                'title' => $title,
                'description' => "Research-focused exploration of {$title} with methodological considerations and practical applications.",
            ]);

            $response->assertCreated(); // Should handle research theme variations
        }
    }
}

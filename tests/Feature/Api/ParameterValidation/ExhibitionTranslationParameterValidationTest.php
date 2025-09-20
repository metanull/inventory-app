<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Exhibition;
use App\Models\ExhibitionTranslation;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive parameter validation tests for ExhibitionTranslation API endpoints
 */
class ExhibitionTranslationParameterValidationTest extends TestCase
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
        $exhibition = Exhibition::factory()->create();
        ExhibitionTranslation::factory()->count(16)->create(['exhibition_id' => $exhibition->id]);

        $response = $this->getJson(route('exhibition-translation.index', [
            'page' => 2,
            'per_page' => 8,
        ]));

        $response->assertOk();
        $response->assertJsonPath('meta.current_page', 2);
        $response->assertJsonPath('meta.per_page', 8);
    }

    public function test_index_accepts_valid_include_parameters()
    {
        $exhibition = Exhibition::factory()->create();
        ExhibitionTranslation::factory()->count(3)->create(['exhibition_id' => $exhibition->id]);

        $response = $this->getJson(route('exhibition-translation.index', [
            'include' => 'exhibition,language',
        ]));

        $response->assertOk();
    }

    public function test_index_rejects_invalid_include_parameters()
    {
        $exhibition = Exhibition::factory()->create();
        ExhibitionTranslation::factory()->count(2)->create(['exhibition_id' => $exhibition->id]);

        $response = $this->getJson(route('exhibition-translation.index', [
            'include' => 'invalid_relation,fake_exhibition,non_existent',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['include']);
    }

    public function test_index_rejects_unexpected_query_parameters_currently()
    {
        $exhibition = Exhibition::factory()->create();
        ExhibitionTranslation::factory()->count(2)->create(['exhibition_id' => $exhibition->id]);

        $response = $this->getJson(route('exhibition-translation.index', [
            'page' => 1,
            'include' => 'exhibition',
            'filter_by_status' => 'active', // Not implemented
            'sort_by_opening' => 'desc', // Not implemented
            'venue_type' => 'museum', // Not implemented
            'admin_access' => true,
            'debug_translations' => true,
            'export_format' => 'xml',
            'bulk_operation' => 'publish_all',
        ]));

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['filter_by_status']);
    }

    // SHOW ENDPOINT TESTS
    public function test_show_accepts_valid_include_parameters()
    {
        $exhibition = Exhibition::factory()->create();
        $translation = ExhibitionTranslation::factory()->create(['exhibition_id' => $exhibition->id]);

        $response = $this->getJson(route('exhibition-translation.show', $translation).'?include=exhibition,language');

        $response->assertOk();
    }

    public function test_show_rejects_unexpected_query_parameters_currently()
    {
        $exhibition = Exhibition::factory()->create();
        $translation = ExhibitionTranslation::factory()->create(['exhibition_id' => $exhibition->id]);

        $response = $this->getJson(route('exhibition-translation.show', $translation).'?include=exhibition&show_details=true&visitor_info=full');

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['show_details']);
    }

    // STORE ENDPOINT TESTS
    public function test_store_validates_required_fields()
    {
        $response = $this->postJson(route('exhibition-translation.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['exhibition_id', 'language_code']);
    }

    public function test_store_validates_exhibition_id_exists()
    {
        $language = Language::factory()->create();

        $response = $this->postJson(route('exhibition-translation.store'), [
            'exhibition_id' => 'non-existent-uuid',
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['exhibition_id']);
    }

    public function test_store_validates_language_code_exists()
    {
        $exhibition = Exhibition::factory()->create();

        $response = $this->postJson(route('exhibition-translation.store'), [
            'exhibition_id' => $exhibition->id,
            'language_code' => 'xyz', // Invalid language code
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_code']);
    }

    public function test_store_validates_unique_combination()
    {
        $exhibition = Exhibition::factory()->create();
        $language = Language::factory()->create();
        ExhibitionTranslation::factory()->create([
            'exhibition_id' => $exhibition->id,
            'language_code' => $language->code,
        ]);

        $response = $this->postJson(route('exhibition-translation.store'), [
            'exhibition_id' => $exhibition->id,
            'language_code' => $language->code, // Duplicate combination
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['exhibition_id']);
    }

    public function test_store_validates_exhibition_id_uuid_format()
    {
        $language = Language::factory()->create();

        $response = $this->postJson(route('exhibition-translation.store'), [
            'exhibition_id' => 'not-a-uuid',
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['exhibition_id']);
    }

    public function test_store_validates_language_code_format()
    {
        $exhibition = Exhibition::factory()->create();

        $response = $this->postJson(route('exhibition-translation.store'), [
            'exhibition_id' => $exhibition->id,
            'language_code' => 'toolong', // Should be 3 characters
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_code']);
    }

    public function test_store_prohibits_id_field()
    {
        $exhibition = Exhibition::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('exhibition-translation.store'), [
            'id' => 'some-uuid', // Should be prohibited
            'exhibition_id' => $exhibition->id,
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_store_accepts_valid_data()
    {
        $exhibition = Exhibition::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('exhibition-translation.store'), [
            'exhibition_id' => $exhibition->id,
            'language_code' => $language->code,
            'title' => 'Translated Exhibition Title',
            'description' => 'Translated exhibition description',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.exhibition_id', $exhibition->id);
        $response->assertJsonPath('data.language_code', $language->code);
    }

    public function test_store_rejects_unexpected_request_parameters_currently()
    {
        $exhibition = Exhibition::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('exhibition-translation.store'), [
            'exhibition_id' => $exhibition->id,
            'language_code' => $language->code,
            'title' => 'Test Exhibition Title',
            'description' => 'Test exhibition description',
            'unexpected_field' => 'should_be_rejected',
            'curator_notes' => 'Internal curator notes', // Not implemented
            'venue_address' => '123 Museum Street', // Not implemented
            'opening_hours' => '9 AM - 5 PM', // Not implemented
            'ticket_price' => '$15.00', // Not implemented
            'admin_created' => true,
            'debug_mode' => true,
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_validates_unique_combination_on_change()
    {
        $exhibition1 = Exhibition::factory()->create();
        $exhibition2 = Exhibition::factory()->create();
        $language = Language::factory()->create();

        $translation1 = ExhibitionTranslation::factory()->create([
            'exhibition_id' => $exhibition1->id,
            'language_code' => $language->code,
        ]);

        ExhibitionTranslation::factory()->create([
            'exhibition_id' => $exhibition2->id,
            'language_code' => $language->code,
        ]);

        $response = $this->putJson(route('exhibition-translation.update', $translation1), [
            'exhibition_id' => $exhibition2->id, // Would create duplicate
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['exhibition_id']);
    }

    public function test_update_prohibits_id_modification()
    {
        $exhibition = Exhibition::factory()->create();
        $translation = ExhibitionTranslation::factory()->create(['exhibition_id' => $exhibition->id]);

        $response = $this->putJson(route('exhibition-translation.update', $translation), [
            'id' => 'new-uuid', // Should be prohibited
            'exhibition_id' => $exhibition->id,
            'language_code' => $translation->language_code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_update_accepts_same_combination()
    {
        $exhibition = Exhibition::factory()->create();
        $translation = ExhibitionTranslation::factory()->create(['exhibition_id' => $exhibition->id]);

        $response = $this->putJson(route('exhibition-translation.update', $translation), [
            'exhibition_id' => $translation->exhibition_id, // Same combination
            'language_code' => $translation->language_code,
            'title' => 'Updated exhibition title',
        ]);

        $response->assertOk();
    }

    public function test_update_rejects_unexpected_request_parameters_currently()
    {
        $exhibition = Exhibition::factory()->create();
        $translation = ExhibitionTranslation::factory()->create(['exhibition_id' => $exhibition->id]);

        $response = $this->putJson(route('exhibition-translation.update', $translation), [
            'exhibition_id' => $translation->exhibition_id,
            'language_code' => $translation->language_code,
            'title' => 'Updated Exhibition Translation',
            'unexpected_field' => 'should_be_rejected',
            'change_status' => 'published',
            'update_priority' => 'high',
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // EDGE CASE TESTS
    public function test_handles_unicode_characters_in_translation_fields()
    {
        $exhibition = Exhibition::factory()->create();

        $unicodeTitles = [
            'Exposition française d\'art moderne',
            'Русская выставка современного искусства',
            '日本の現代美術展覧会',
            'معرض الفن العربي المعاصر',
            'Exposición española de arte contemporáneo',
            'Mostra italiana di arte contemporanea',
            'Wystawa polskiego sztuki współczesnej',
            'Ελληνική έκθεση σύγχρονης τέχνης',
            'Dansk udstilling af moderne kunst',
            'Magyar kortárs művészeti kiállítás',
        ];

        foreach ($unicodeTitles as $index => $title) {
            $newLanguage = Language::factory()->create(['code' => sprintf('%03d', $index)]);

            $response = $this->postJson(route('exhibition-translation.store'), [
                'exhibition_id' => $exhibition->id,
                'language_code' => $newLanguage->code,
                'title' => $title,
                'description' => "Description for {$title}",
            ]);

            $response->assertCreated(); // Should handle Unicode gracefully
        }
    }

    public function test_handles_empty_and_null_optional_fields()
    {
        $exhibition = Exhibition::factory()->create();
        $translation = ExhibitionTranslation::factory()->create(['exhibition_id' => $exhibition->id]);

        $testCases = [
            ['title' => null, 'description' => null],
            ['title' => '', 'description' => ''],
            ['title' => '   ', 'description' => '   '], // Whitespace only
            ['title' => 'Valid Title', 'description' => null],
            ['title' => null, 'description' => 'Valid Description'],
        ];

        foreach ($testCases as $data) {
            $updateData = array_merge([
                'exhibition_id' => $translation->exhibition_id,
                'language_code' => $translation->language_code,
            ], $data);

            $response = $this->putJson(route('exhibition-translation.update', $translation), $updateData);

            // Should handle gracefully
            $this->assertContains($response->status(), [200, 422]);
        }
    }

    public function test_handles_very_long_translation_content()
    {
        $exhibition = Exhibition::factory()->create();
        $language = Language::factory()->create();

        $veryLongTitle = str_repeat('Very Long Exhibition Title With Extended Cultural Context ', 10);
        $veryLongDescription = str_repeat('Very Long Exhibition Description With Detailed Historical Background And Comprehensive Artistic Analysis ', 15);

        $response = $this->postJson(route('exhibition-translation.store'), [
            'exhibition_id' => $exhibition->id,
            'language_code' => $language->code,
            'title' => $veryLongTitle,
            'description' => $veryLongDescription,
        ]);

        // Should handle gracefully
        $this->assertContains($response->status(), [201, 422]);
    }

    public function test_handles_array_injection_attempts()
    {
        $exhibition = Exhibition::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('exhibition-translation.store'), [
            'exhibition_id' => ['array' => 'instead_of_uuid'],
            'language_code' => ['malicious' => 'array'],
            'title' => ['injection' => 'attempt'],
            'description' => ['another' => 'injection'],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['exhibition_id']);
    }

    public function test_pagination_with_many_translations()
    {
        $exhibition = Exhibition::factory()->create();
        ExhibitionTranslation::factory()->count(54)->create(['exhibition_id' => $exhibition->id]);

        $testCases = [
            ['page' => 1, 'per_page' => 18],
            ['page' => 2, 'per_page' => 20],
            ['page' => 1, 'per_page' => 100], // Maximum
        ];

        foreach ($testCases as $params) {
            $response = $this->getJson(route('exhibition-translation.index', $params));
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
            $response = $this->getJson(route('exhibition-translation.index', $params));
            $response->assertUnprocessable();
        }
    }

    public function test_handles_special_characters_in_translation_content()
    {
        $exhibition = Exhibition::factory()->create();

        $specialCharTitles = [
            'Exhibition "With Quotes" Here',
            "Exhibition 'With Apostrophes' Content",
            'Exhibition & Symbol Content',
            'Exhibition: Colon Content',
            'Exhibition (Parentheses) Content',
            'Exhibition - Dash Content',
            'Exhibition @ Symbol Content',
            'Exhibition #Hashtag Content',
            'Exhibition 50% Percentage',
            'Exhibition $Dollar Content',
            'Exhibition *Asterisk Content',
            'Exhibition +Plus Content',
            'Exhibition =Equals Content',
            'Exhibition |Pipe Content',
        ];

        foreach ($specialCharTitles as $index => $title) {
            $newLanguage = Language::factory()->create(['code' => sprintf('s%02d', $index)]);

            $response = $this->postJson(route('exhibition-translation.store'), [
                'exhibition_id' => $exhibition->id,
                'language_code' => $newLanguage->code,
                'title' => $title,
                'description' => "Description for {$title}",
            ]);

            // Should handle gracefully
            $this->assertContains($response->status(), [201, 422]);
        }
    }

    public function test_handles_exhibition_translation_workflow()
    {
        $exhibition = Exhibition::factory()->create();
        $english = Language::factory()->create(['code' => 'eng']);
        $french = Language::factory()->create(['code' => 'fra']);
        $spanish = Language::factory()->create(['code' => 'spa']);

        // Create translations in different languages
        $languages = [$english, $french, $spanish];
        $titles = [
            'Ancient Civilizations: Treasures from the Mediterranean',
            'Civilisations Antiques: Trésors de la Méditerranée',
            'Civilizaciones Antiguas: Tesoros del Mediterráneo',
        ];
        $descriptions = [
            'This groundbreaking exhibition showcases artifacts from ancient Mediterranean civilizations...',
            'Cette exposition révolutionnaire présente des artefacts des civilisations méditerranéennes antiques...',
            'Esta exposición innovadora presenta artefactos de las civilizaciones mediterráneas antiguas...',
        ];

        foreach ($languages as $index => $language) {
            $response = $this->postJson(route('exhibition-translation.store'), [
                'exhibition_id' => $exhibition->id,
                'language_code' => $language->code,
                'title' => $titles[$index],
                'description' => $descriptions[$index],
            ]);

            $response->assertCreated();
        }

        // Verify all translations exist
        $indexResponse = $this->getJson(route('exhibition-translation.index'));
        $indexResponse->assertOk();
        $indexResponse->assertJsonPath('meta.total', 3);
    }

    public function test_handles_exhibition_type_variations()
    {
        $exhibition = Exhibition::factory()->create();

        $exhibitionTypes = [
            'Permanent Collection Display',
            'Temporary Special Exhibition',
            'Traveling Exhibition',
            'Digital Virtual Exhibition',
            'Interactive Multimedia Exhibition',
            'Outdoor Sculpture Exhibition',
            'Community Collaborative Exhibition',
            'Educational Workshop Exhibition',
            'Artist Retrospective Exhibition',
            'Thematic Historical Exhibition',
            'Contemporary Art Showcase',
            'Archaeological Discovery Exhibition',
            'Cultural Heritage Celebration',
            'Annual Members Exhibition',
            'Student Research Exhibition',
        ];

        foreach ($exhibitionTypes as $index => $type) {
            $newLanguage = Language::factory()->create(['code' => sprintf('t%02d', $index)]);

            $response = $this->postJson(route('exhibition-translation.store'), [
                'exhibition_id' => $exhibition->id,
                'language_code' => $newLanguage->code,
                'title' => $type,
                'description' => "A comprehensive {$type} featuring significant works from our collection.",
            ]);

            $response->assertCreated(); // Should handle exhibition type variations
        }
    }

    public function test_handles_description_format_variations()
    {
        $exhibition = Exhibition::factory()->create();

        $descriptionFormats = [
            'A groundbreaking exhibition featuring over 200 artifacts from ancient civilizations.',
            'This comprehensive display showcases the evolution of artistic expression through the ages.',
            'Visitors will explore interactive installations that bring history to life.',
            'The exhibition includes rare manuscripts, sculptures, and digital reconstructions.',
            'Experience the cultural heritage of diverse civilizations through immersive storytelling.',
            'Curated by leading experts, this show presents new archaeological discoveries.',
            'Educational programs and workshops complement the main exhibition displays.',
            'Multimedia presentations enhance understanding of historical contexts.',
            'Special loans from international museums create a unique viewing experience.',
            'Conservation efforts are highlighted through behind-the-scenes demonstrations.',
            'Family-friendly activities and guided tours are available throughout the run.',
            'Research findings and scholarly interpretations provide deeper insights.',
            'Interactive technology allows visitors to explore artifacts in detail.',
            'The exhibition catalog includes essays by renowned historians.',
            'Public programming includes lectures, symposiums, and cultural performances.',
        ];

        foreach ($descriptionFormats as $index => $description) {
            $newLanguage = Language::factory()->create(['code' => sprintf('d%02d', $index)]);

            $response = $this->postJson(route('exhibition-translation.store'), [
                'exhibition_id' => $exhibition->id,
                'language_code' => $newLanguage->code,
                'title' => "Exhibition Title {$index}",
                'description' => $description,
            ]);

            $response->assertCreated(); // Should handle description format variations
        }
    }

    public function test_handles_curatorial_content_variations()
    {
        $exhibition = Exhibition::factory()->create();

        $curatorialContent = [
            'Curated by Dr. Sarah Johnson, Professor of Ancient History at Harvard University.',
            'Guest curator Maria Rodriguez brings 20 years of archaeological expertise.',
            'Collaborative curation between museum staff and community representatives.',
            'Student curators from the Museum Studies program present fresh perspectives.',
            'International curatorial team represents diverse cultural viewpoints.',
            'Emerging curator fellowship program showcases new voices in the field.',
            'Interdisciplinary approach combines art history, archaeology, and anthropology.',
            'Community input and oral histories inform the curatorial narrative.',
            'Digital curation methods employ AI and machine learning technologies.',
            'Participatory curation invites public contribution to exhibition themes.',
            'Decolonizing curatorial practices challenge traditional museum approaches.',
            'Environmental considerations guide sustainable exhibition practices.',
            'Accessibility and inclusion are central to the curatorial framework.',
            'Educational partnerships enhance curatorial content and programming.',
            'Research-driven curation presents latest scholarly developments.',
        ];

        foreach ($curatorialContent as $index => $content) {
            $newLanguage = Language::factory()->create(['code' => sprintf('u%02d', $index)]);

            $response = $this->postJson(route('exhibition-translation.store'), [
                'exhibition_id' => $exhibition->id,
                'language_code' => $newLanguage->code,
                'title' => "Curatorial Exhibition {$index}",
                'description' => $content,
            ]);

            $response->assertCreated(); // Should handle curatorial content variations
        }
    }
}

<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Detail;
use App\Models\DetailTranslation;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive parameter validation tests for DetailTranslation API endpoints
 */
class DetailTranslationParameterValidationTest extends TestCase
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
        $detail = Detail::factory()->create();
        DetailTranslation::factory()->count(16)->create(['detail_id' => $detail->id]);

        $response = $this->getJson(route('detail-translation.index', [
            'page' => 2,
            'per_page' => 8,
        ]));

        $response->assertOk();
        $response->assertJsonPath('meta.current_page', 2);
        $response->assertJsonPath('meta.per_page', 8);
    }

    public function test_index_accepts_valid_include_parameters()
    {
        $detail = Detail::factory()->create();
        DetailTranslation::factory()->count(4)->create(['detail_id' => $detail->id]);

        $response = $this->getJson(route('detail-translation.index', [
            'include' => 'detail,language',
        ]));

        $response->assertOk();
    }

    public function test_index_rejects_invalid_include_parameters()
    {
        $detail = Detail::factory()->create();
        DetailTranslation::factory()->count(2)->create(['detail_id' => $detail->id]);

        $response = $this->getJson(route('detail-translation.index', [
            'include' => 'invalid_relation,fake_detail,non_existent',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['include']);
    }

    public function test_index_rejects_unexpected_query_parameters_currently()
    {
        $detail = Detail::factory()->create();
        DetailTranslation::factory()->count(2)->create(['detail_id' => $detail->id]);

        $response = $this->getJson(route('detail-translation.index', [
            'page' => 1,
            'include' => 'detail',
            'filter_by_item' => 'ABC123', // Not implemented
            'detail_type' => 'technical', // Not implemented
            'complexity' => 'advanced', // Not implemented
            'admin_access' => true,
            'debug_translations' => true,
            'export_format' => 'json',
            'bulk_operation' => 'validate_all',
        ]));

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['filter_by_item']);
    }

    // SHOW ENDPOINT TESTS
    public function test_show_accepts_valid_include_parameters()
    {
        $detail = Detail::factory()->create();
        $translation = DetailTranslation::factory()->create(['detail_id' => $detail->id]);

        $response = $this->getJson(route('detail-translation.show', $translation).'?include=detail,language');

        $response->assertOk();
    }

    public function test_show_rejects_unexpected_query_parameters_currently()
    {
        $detail = Detail::factory()->create();
        $translation = DetailTranslation::factory()->create(['detail_id' => $detail->id]);

        $response = $this->getJson(route('detail-translation.show', $translation).'?include=detail&show_technical=true&curatorial_notes=detailed');

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['show_technical']);
    }

    // STORE ENDPOINT TESTS
    public function test_store_validates_required_fields()
    {
        $response = $this->postJson(route('detail-translation.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['detail_id', 'language_code']);
    }

    public function test_store_validates_detail_id_exists()
    {
        $language = Language::factory()->create();

        $response = $this->postJson(route('detail-translation.store'), [
            'detail_id' => 'non-existent-uuid',
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['detail_id']);
    }

    public function test_store_validates_language_code_exists()
    {
        $detail = Detail::factory()->create();

        $response = $this->postJson(route('detail-translation.store'), [
            'detail_id' => $detail->id,
            'language_code' => 'xyz', // Invalid language code
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_code']);
    }

    public function test_store_validates_unique_combination()
    {
        $detail = Detail::factory()->create();
        $language = Language::factory()->create();
        DetailTranslation::factory()->create([
            'detail_id' => $detail->id,
            'language_code' => $language->code,
        ]);

        $response = $this->postJson(route('detail-translation.store'), [
            'detail_id' => $detail->id,
            'language_code' => $language->code, // Duplicate combination
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['detail_id']);
    }

    public function test_store_validates_detail_id_uuid_format()
    {
        $language = Language::factory()->create();

        $response = $this->postJson(route('detail-translation.store'), [
            'detail_id' => 'not-a-uuid',
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['detail_id']);
    }

    public function test_store_validates_language_code_format()
    {
        $detail = Detail::factory()->create();

        $response = $this->postJson(route('detail-translation.store'), [
            'detail_id' => $detail->id,
            'language_code' => 'toolong', // Should be 3 characters
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_code']);
    }

    public function test_store_prohibits_id_field()
    {
        $detail = Detail::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('detail-translation.store'), [
            'id' => 'some-uuid', // Should be prohibited
            'detail_id' => $detail->id,
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_store_accepts_valid_data()
    {
        $detail = Detail::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('detail-translation.store'), [
            'detail_id' => $detail->id,
            'language_code' => $language->code,
            'content' => 'Translated detail content',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.detail_id', $detail->id);
        $response->assertJsonPath('data.language_code', $language->code);
    }

    public function test_store_rejects_unexpected_request_parameters_currently()
    {
        $detail = Detail::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('detail-translation.store'), [
            'detail_id' => $detail->id,
            'language_code' => $language->code,
            'content' => 'Test Detail Translation',
            'unexpected_field' => 'should_be_rejected',
            'technical_notes' => 'Advanced technical information', // Not implemented
            'conservation_status' => 'Stable', // Not implemented
            'research_notes' => 'Ongoing research', // Not implemented
            'curatorial_comments' => 'Important piece', // Not implemented
            'admin_created' => true,
            'malicious_embed' => '<embed src="javascript:alert(\'XSS\')">',
            'sql_injection' => "'; DROP TABLE detail_translations; --",
            'privilege_escalation' => 'detail_admin',
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_validates_unique_combination_on_change()
    {
        $detail1 = Detail::factory()->create();
        $detail2 = Detail::factory()->create();
        $language = Language::factory()->create();

        $translation1 = DetailTranslation::factory()->create([
            'detail_id' => $detail1->id,
            'language_code' => $language->code,
        ]);

        DetailTranslation::factory()->create([
            'detail_id' => $detail2->id,
            'language_code' => $language->code,
        ]);

        $response = $this->putJson(route('detail-translation.update', $translation1), [
            'detail_id' => $detail2->id, // Would create duplicate
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['detail_id']);
    }

    public function test_update_prohibits_id_modification()
    {
        $detail = Detail::factory()->create();
        $translation = DetailTranslation::factory()->create(['detail_id' => $detail->id]);

        $response = $this->putJson(route('detail-translation.update', $translation), [
            'id' => 'new-uuid', // Should be prohibited
            'detail_id' => $detail->id,
            'language_code' => $translation->language_code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_update_accepts_same_combination()
    {
        $detail = Detail::factory()->create();
        $translation = DetailTranslation::factory()->create(['detail_id' => $detail->id]);

        $response = $this->putJson(route('detail-translation.update', $translation), [
            'detail_id' => $translation->detail_id, // Same combination
            'language_code' => $translation->language_code,
            'content' => 'Updated detail content',
        ]);

        $response->assertOk();
    }

    public function test_update_rejects_unexpected_request_parameters_currently()
    {
        $detail = Detail::factory()->create();
        $translation = DetailTranslation::factory()->create(['detail_id' => $detail->id]);

        $response = $this->putJson(route('detail-translation.update', $translation), [
            'detail_id' => $translation->detail_id,
            'language_code' => $translation->language_code,
            'content' => 'Updated Detail Translation',
            'unexpected_field' => 'should_be_rejected',
            'change_status' => 'verified',
            'update_metadata' => 'comprehensive',
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // EDGE CASE TESTS
    public function test_handles_unicode_characters_in_translation_fields()
    {
        $detail = Detail::factory()->create();

        $unicodeDetailContent = [
            'Détail français avec caractères spéciaux',
            'Деталь русская с особыми символами',
            '詳細日本語の特殊文字付き',
            'تفاصيل عربية مع رموز خاصة',
            'Detalle español con caracteres especiales',
            'Dettaglio italiano con caratteri speciali',
            'Szczegół polski ze znakami specjalnymi',
            'Λεπτομέρεια ελληνική με ειδικούς χαρακτήρες',
            'Detalje dansk med særlige tegn',
            'Részlet magyar speciális karakterekkel',
        ];

        foreach ($unicodeDetailContent as $index => $content) {
            $newLanguage = Language::factory()->create(['code' => sprintf('%03d', $index)]);

            $response = $this->postJson(route('detail-translation.store'), [
                'detail_id' => $detail->id,
                'language_code' => $newLanguage->code,
                'content' => $content,
            ]);

            $response->assertCreated(); // Should handle Unicode gracefully
        }
    }

    public function test_handles_empty_and_null_optional_fields()
    {
        $detail = Detail::factory()->create();
        $translation = DetailTranslation::factory()->create(['detail_id' => $detail->id]);

        $testCases = [
            ['content' => null],
            ['content' => ''],
            ['content' => '   '], // Whitespace only
        ];

        foreach ($testCases as $data) {
            $updateData = array_merge([
                'detail_id' => $translation->detail_id,
                'language_code' => $translation->language_code,
            ], $data);

            $response = $this->putJson(route('detail-translation.update', $translation), $updateData);

            // Should handle gracefully
            $this->assertContains($response->status(), [200, 422]);
        }
    }

    public function test_handles_very_long_translation_content()
    {
        $detail = Detail::factory()->create();
        $language = Language::factory()->create();

        $veryLongContent = str_repeat('Very Long Detail Content With Extended Technical Description And Comprehensive Analysis And Detailed Curatorial Notes And Conservation Information And Research Findings And Historical Context ', 10);

        $response = $this->postJson(route('detail-translation.store'), [
            'detail_id' => $detail->id,
            'language_code' => $language->code,
            'content' => $veryLongContent,
        ]);

        // Should handle gracefully
        $this->assertContains($response->status(), [201, 422]);
    }

    public function test_handles_array_injection_attempts()
    {
        $detail = Detail::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('detail-translation.store'), [
            'detail_id' => ['array' => 'instead_of_uuid'],
            'language_code' => ['malicious' => 'array'],
            'content' => ['injection' => 'attempt'],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['detail_id']);
    }

    public function test_pagination_with_many_translations()
    {
        $detail = Detail::factory()->create();
        DetailTranslation::factory()->count(55)->create(['detail_id' => $detail->id]);

        $testCases = [
            ['page' => 1, 'per_page' => 18],
            ['page' => 2, 'per_page' => 22],
            ['page' => 1, 'per_page' => 100], // Maximum
        ];

        foreach ($testCases as $params) {
            $response = $this->getJson(route('detail-translation.index', $params));
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
            $response = $this->getJson(route('detail-translation.index', $params));
            $response->assertUnprocessable();
        }
    }

    public function test_handles_special_characters_in_translation_content()
    {
        $detail = Detail::factory()->create();

        $specialCharContent = [
            'Detail "with quoted content" here',
            "Detail 'with apostrophes' content",
            'Detail & symbol content',
            'Detail: colon content',
            'Detail (parentheses) content',
            'Detail - dash content',
            'Detail @ symbol content',
            'Detail #hashtag content',
            'Detail 50% percentage',
            'Detail $dollar content',
            'Detail *asterisk content',
            'Detail +plus content',
            'Detail =equals content',
            'Detail |pipe content',
        ];

        foreach ($specialCharContent as $index => $content) {
            $newLanguage = Language::factory()->create(['code' => sprintf('d%02d', $index)]);

            $response = $this->postJson(route('detail-translation.store'), [
                'detail_id' => $detail->id,
                'language_code' => $newLanguage->code,
                'content' => $content,
            ]);

            // Should handle gracefully
            $this->assertContains($response->status(), [201, 422]);
        }
    }

    public function test_handles_detail_translation_workflow()
    {
        $detail = Detail::factory()->create();
        $english = Language::factory()->create(['code' => 'eng']);
        $french = Language::factory()->create(['code' => 'fra']);
        $spanish = Language::factory()->create(['code' => 'spa']);

        // Create translations in different languages
        $languages = [$english, $french, $spanish];
        $contents = [
            'Technical specifications and detailed analysis',
            'Spécifications techniques et analyse détaillée',
            'Especificaciones técnicas y análisis detallado',
        ];

        foreach ($languages as $index => $language) {
            $response = $this->postJson(route('detail-translation.store'), [
                'detail_id' => $detail->id,
                'language_code' => $language->code,
                'content' => $contents[$index],
            ]);

            $response->assertCreated();
        }

        // Verify all translations exist
        $indexResponse = $this->getJson(route('detail-translation.index'));
        $indexResponse->assertOk();
        $indexResponse->assertJsonPath('meta.total', 3);
    }

    public function test_handles_technical_detail_content_variations()
    {
        $detail = Detail::factory()->create();

        $technicalContents = [
            'Material Analysis: Bronze alloy with traces of tin and lead',
            'Conservation Status: Stable, minor surface corrosion present',
            'Provenance: Excavated from Site XYZ, Layer 3, Grid A4',
            'Dating: Radiocarbon dated to 1450-1520 CE',
            'Dimensions: Height 15.2cm, Width 8.7cm, Depth 3.4cm',
            'Weight: 234.7 grams',
            'Condition Report: Good overall, handle partially restored',
            'X-ray Analysis: Shows internal structure intact',
            'Chemical Composition: 87% copper, 11% tin, 2% other metals',
            'Manufacturing Technique: Lost-wax casting method',
            'Surface Treatment: Evidence of original gilding',
            'Wear Patterns: Consistent with ceremonial use',
            'Comparative Studies: Similar to examples in Berlin Museum',
            'Bibliography: Published in Journal of Archaeology, 2019',
            'Digital Documentation: 3D scan completed, file ID: 3D_2024_001',
        ];

        foreach ($technicalContents as $index => $content) {
            $newLanguage = Language::factory()->create(['code' => sprintf('t%02d', $index)]);

            $response = $this->postJson(route('detail-translation.store'), [
                'detail_id' => $detail->id,
                'language_code' => $newLanguage->code,
                'content' => $content,
            ]);

            $response->assertCreated(); // Should handle technical detail variations
        }
    }

    public function test_handles_formatted_content()
    {
        $detail = Detail::factory()->create();

        $formattedContents = [
            "Line 1\nLine 2\nLine 3",
            "Paragraph 1\n\nParagraph 2",
            "Item 1\n- Subitem A\n- Subitem B",
            "Section:\n  Subsection 1\n  Subsection 2",
            "Title\n=====\nContent here",
            "1. First point\n2. Second point\n3. Third point",
            "Key: Value\nAnother Key: Another Value",
            "Data:\n  Field1: Content\n  Field2: More content",
            "Header\n------\nBody text continues here",
            "A. Main point\n  i. Detail\n  ii. Another detail",
        ];

        foreach ($formattedContents as $index => $content) {
            $newLanguage = Language::factory()->create(['code' => sprintf('f%02d', $index)]);

            $response = $this->postJson(route('detail-translation.store'), [
                'detail_id' => $detail->id,
                'language_code' => $newLanguage->code,
                'content' => $content,
            ]);

            $response->assertCreated(); // Should handle formatted content
        }
    }
}

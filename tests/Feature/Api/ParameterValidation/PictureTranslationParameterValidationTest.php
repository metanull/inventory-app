<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Language;
use App\Models\Picture;
use App\Models\PictureTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive parameter validation tests for PictureTranslation API endpoints
 */
class PictureTranslationParameterValidationTest extends TestCase
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
        $picture = Picture::factory()->create();
        PictureTranslation::factory()->count(14)->create(['picture_id' => $picture->id]);

        $response = $this->getJson(route('picture-translation.index', [
            'page' => 2,
            'per_page' => 7,
        ]));

        $response->assertOk();
        $response->assertJsonPath('meta.current_page', 2);
        $response->assertJsonPath('meta.per_page', 7);
    }

    public function test_index_accepts_valid_include_parameters()
    {
        $picture = Picture::factory()->create();
        PictureTranslation::factory()->count(3)->create(['picture_id' => $picture->id]);

        $response = $this->getJson(route('picture-translation.index', [
            'include' => 'picture,language',
        ]));

        $response->assertOk();
    }

    public function test_index_rejects_invalid_include_parameters()
    {
        $picture = Picture::factory()->create();
        PictureTranslation::factory()->count(2)->create(['picture_id' => $picture->id]);

        $response = $this->getJson(route('picture-translation.index', [
            'include' => 'invalid_relation,fake_picture,non_existent',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['include']);
    }

    public function test_index_rejects_unexpected_query_parameters_currently()
    {
        // SECURITY TEST: Validates Form Request security with parameter whitelisting
        $picture = Picture::factory()->create();
        PictureTranslation::factory()->count(2)->create(['picture_id' => $picture->id]);

        $response = $this->getJson(route('picture-translation.index', [
            'page' => 1,
            'include' => 'picture',
            'filter_by_type' => 'photograph', // Not implemented
            'resolution' => 'high', // Not implemented
            'color_mode' => 'rgb', // Not implemented
            'admin_access' => true,
            'debug_translations' => true,
            'export_format' => 'xml',
            'bulk_operation' => 'optimize_all',
        ]));

        $response->assertStatus(422); // Must reject unexpected params for security
        $response->assertJsonValidationErrors([
            'filter_by_type', 'resolution', 'color_mode', 'admin_access', 'debug_translations', 'export_format', 'bulk_operation',
        ]);
    }

    // SHOW ENDPOINT TESTS
    public function test_show_accepts_valid_include_parameters()
    {
        $picture = Picture::factory()->create();
        $translation = PictureTranslation::factory()->create(['picture_id' => $picture->id]);

        $response = $this->getJson(route('picture-translation.show', $translation).'?include=picture,language');

        $response->assertOk();
    }

    public function test_show_rejects_unexpected_query_parameters_currently()
    {
        // SECURITY TEST: Validates Form Request security with parameter whitelisting
        $picture = Picture::factory()->create();
        $translation = PictureTranslation::factory()->create(['picture_id' => $picture->id]);

        $response = $this->getJson(route('picture-translation.show', $translation).'?include=picture&show_metadata=true&image_details=full');

        $response->assertStatus(422); // Must reject unexpected params for security
        $response->assertJsonValidationErrors(['show_metadata', 'image_details']);
    }

    // STORE ENDPOINT TESTS
    public function test_store_validates_required_fields()
    {
        $response = $this->postJson(route('picture-translation.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['picture_id', 'language_code']);
    }

    public function test_store_validates_picture_id_exists()
    {
        $language = Language::factory()->create();

        $response = $this->postJson(route('picture-translation.store'), [
            'picture_id' => 'non-existent-uuid',
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['picture_id']);
    }

    public function test_store_validates_language_code_exists()
    {
        $picture = Picture::factory()->create();

        $response = $this->postJson(route('picture-translation.store'), [
            'picture_id' => $picture->id,
            'language_code' => 'xyz', // Invalid language code
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_code']);
    }

    public function test_store_validates_unique_combination()
    {
        $picture = Picture::factory()->create();
        $language = Language::factory()->create();
        PictureTranslation::factory()->create([
            'picture_id' => $picture->id,
            'language_code' => $language->code,
        ]);

        $response = $this->postJson(route('picture-translation.store'), [
            'picture_id' => $picture->id,
            'language_code' => $language->code, // Duplicate combination
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['picture_id']);
    }

    public function test_store_validates_picture_id_uuid_format()
    {
        $language = Language::factory()->create();

        $response = $this->postJson(route('picture-translation.store'), [
            'picture_id' => 'not-a-uuid',
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['picture_id']);
    }

    public function test_store_validates_language_code_format()
    {
        $picture = Picture::factory()->create();

        $response = $this->postJson(route('picture-translation.store'), [
            'picture_id' => $picture->id,
            'language_code' => 'toolong', // Should be 3 characters
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_code']);
    }

    public function test_store_prohibits_id_field()
    {
        $picture = Picture::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('picture-translation.store'), [
            'id' => 'some-uuid', // Should be prohibited
            'picture_id' => $picture->id,
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_store_accepts_valid_data()
    {
        $picture = Picture::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('picture-translation.store'), [
            'picture_id' => $picture->id,
            'language_code' => $language->code,
            'alt_text' => 'Translated alt text',
            'caption' => 'Translated caption',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.picture_id', $picture->id);
        $response->assertJsonPath('data.language_code', $language->code);
    }

    public function test_store_rejects_unexpected_request_parameters_currently()
    {
        // SECURITY TEST: Universal parameter injection vulnerability protection
        $picture = Picture::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('picture-translation.store'), [
            'picture_id' => $picture->id,
            'language_code' => $language->code,
            'alt_text' => 'Test Picture Alt Text',
            'caption' => 'Test caption',
            'unexpected_field' => 'should_be_rejected',
            'photographer' => 'John Doe', // Not implemented
            'copyright_notice' => '© 2024 Museum', // Not implemented
            'technical_specs' => 'Canon EOS R5, 85mm', // Not implemented
            'location_taken' => 'Studio A', // Not implemented
            'admin_created' => true,
            'malicious_svg' => '<svg onload="alert(\'XSS\')"></svg>',
            'sql_injection' => "'; DROP TABLE picture_translations; --",
            'privilege_escalation' => 'media_admin',
        ]);

        $response->assertStatus(422); // Must reject unexpected params for security
        $response->assertJsonValidationErrors([
            'unexpected_field', 'photographer', 'copyright_notice', 'technical_specs', 'location_taken',
            'admin_created', 'malicious_svg', 'sql_injection', 'privilege_escalation',
        ]);
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_validates_unique_combination_on_change()
    {
        $picture1 = Picture::factory()->create();
        $picture2 = Picture::factory()->create();
        $language = Language::factory()->create();

        $translation1 = PictureTranslation::factory()->create([
            'picture_id' => $picture1->id,
            'language_code' => $language->code,
        ]);

        PictureTranslation::factory()->create([
            'picture_id' => $picture2->id,
            'language_code' => $language->code,
        ]);

        $response = $this->putJson(route('picture-translation.update', $translation1), [
            'picture_id' => $picture2->id, // Would create duplicate
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['picture_id']);
    }

    public function test_update_prohibits_id_modification()
    {
        $picture = Picture::factory()->create();
        $translation = PictureTranslation::factory()->create(['picture_id' => $picture->id]);

        $response = $this->putJson(route('picture-translation.update', $translation), [
            'id' => 'new-uuid', // Should be prohibited
            'picture_id' => $picture->id,
            'language_code' => $translation->language_code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_update_accepts_same_combination()
    {
        $picture = Picture::factory()->create();
        $translation = PictureTranslation::factory()->create(['picture_id' => $picture->id]);

        $response = $this->putJson(route('picture-translation.update', $translation), [
            'picture_id' => $translation->picture_id, // Same combination
            'language_code' => $translation->language_code,
            'alt_text' => 'Updated alt text',
        ]);

        $response->assertOk();
    }

    public function test_update_rejects_unexpected_request_parameters_currently()
    {
        // SECURITY TEST: Current behavior must reject unexpected parameters
        $picture = Picture::factory()->create();
        $translation = PictureTranslation::factory()->create(['picture_id' => $picture->id]);

        $response = $this->putJson(route('picture-translation.update', $translation), [
            'picture_id' => $translation->picture_id,
            'language_code' => $translation->language_code,
            'alt_text' => 'Updated Picture Translation',
            'unexpected_field' => 'should_be_rejected',
            'change_status' => 'verified',
            'update_quality' => 'enhanced',
        ]);

        $response->assertStatus(422); // Must reject unexpected params for security
        $response->assertJsonValidationErrors(['unexpected_field', 'change_status', 'update_quality']);
    }

    // EDGE CASE TESTS
    public function test_handles_unicode_characters_in_translation_fields()
    {
        $picture = Picture::factory()->create();

        $unicodeAltTexts = [
            'Image française avec description',
            'Изображение русское с описанием',
            '画像日本語の説明付き',
            'صورة عربية مع وصف',
            'Imagen española con descripción',
            'Immagine italiana con descrizione',
            'Obraz polski z opisem',
            'Εικόνα ελληνική με περιγραφή',
            'Billede dansk med beskrivelse',
            'Kép magyar leírással',
        ];

        foreach ($unicodeAltTexts as $index => $altText) {
            $newLanguage = Language::factory()->create(['code' => sprintf('%03d', $index)]);

            $response = $this->postJson(route('picture-translation.store'), [
                'picture_id' => $picture->id,
                'language_code' => $newLanguage->code,
                'alt_text' => $altText,
                'caption' => "Caption for {$altText}",
            ]);

            $response->assertCreated(); // Should handle Unicode gracefully
        }
    }

    public function test_handles_empty_and_null_optional_fields()
    {
        $picture = Picture::factory()->create();
        $translation = PictureTranslation::factory()->create(['picture_id' => $picture->id]);

        $testCases = [
            ['alt_text' => null, 'caption' => null],
            ['alt_text' => '', 'caption' => ''],
            ['alt_text' => '   ', 'caption' => '   '], // Whitespace only
            ['alt_text' => 'Valid Alt Text', 'caption' => null],
            ['alt_text' => null, 'caption' => 'Valid Caption'],
        ];

        foreach ($testCases as $data) {
            $updateData = array_merge([
                'picture_id' => $translation->picture_id,
                'language_code' => $translation->language_code,
            ], $data);

            $response = $this->putJson(route('picture-translation.update', $translation), $updateData);

            // Should handle gracefully
            $this->assertContains($response->status(), [200, 422]);
        }
    }

    public function test_handles_very_long_translation_content()
    {
        $picture = Picture::factory()->create();
        $language = Language::factory()->create();

        $veryLongAltText = str_repeat('Very Long Alt Text With Extended Image Description ', 30);
        $veryLongCaption = str_repeat('Very Long Caption With Detailed Historical Context And Technical Information About The Photograph ', 20);

        $response = $this->postJson(route('picture-translation.store'), [
            'picture_id' => $picture->id,
            'language_code' => $language->code,
            'alt_text' => $veryLongAltText,
            'caption' => $veryLongCaption,
        ]);

        // Should handle gracefully
        $this->assertContains($response->status(), [201, 422]);
    }

    public function test_handles_array_injection_attempts()
    {
        $picture = Picture::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('picture-translation.store'), [
            'picture_id' => ['array' => 'instead_of_uuid'],
            'language_code' => ['malicious' => 'array'],
            'alt_text' => ['injection' => 'attempt'],
            'caption' => ['another' => 'injection'],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['picture_id']);
    }

    public function test_pagination_with_many_translations()
    {
        $picture = Picture::factory()->create();
        PictureTranslation::factory()->count(42)->create(['picture_id' => $picture->id]);

        $testCases = [
            ['page' => 1, 'per_page' => 14],
            ['page' => 2, 'per_page' => 16],
            ['page' => 1, 'per_page' => 100], // Maximum
        ];

        foreach ($testCases as $params) {
            $response = $this->getJson(route('picture-translation.index', $params));
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
            $response = $this->getJson(route('picture-translation.index', $params));
            $response->assertUnprocessable();
        }
    }

    public function test_handles_special_characters_in_translation_content()
    {
        $picture = Picture::factory()->create();

        $specialCharAltTexts = [
            'Alt text "with quotes" here',
            "Alt text 'with apostrophes' content",
            'Alt text & symbol content',
            'Alt text: colon content',
            'Alt text (parentheses) content',
            'Alt text - dash content',
            'Alt text @ symbol content',
            'Alt text #hashtag content',
            'Alt text 50% percentage',
            'Alt text $dollar content',
            'Alt text *asterisk content',
            'Alt text +plus content',
            'Alt text =equals content',
            'Alt text |pipe content',
        ];

        foreach ($specialCharAltTexts as $index => $altText) {
            $newLanguage = Language::factory()->create(['code' => sprintf('p%02d', $index)]);

            $response = $this->postJson(route('picture-translation.store'), [
                'picture_id' => $picture->id,
                'language_code' => $newLanguage->code,
                'alt_text' => $altText,
                'caption' => "Caption for {$altText}",
            ]);

            // Should handle gracefully
            $this->assertContains($response->status(), [201, 422]);
        }
    }

    public function test_handles_picture_translation_workflow()
    {
        $picture = Picture::factory()->create();
        $english = Language::factory()->create(['code' => 'eng']);
        $french = Language::factory()->create(['code' => 'fra']);
        $spanish = Language::factory()->create(['code' => 'spa']);

        // Create translations in different languages
        $languages = [$english, $french, $spanish];
        $altTexts = [
            'Ancient bronze vessel from Roman period',
            'Vase en bronze ancien de la période romaine',
            'Vasija de bronce antigua del período romano',
        ];
        $captions = [
            'Figure 1: Bronze vessel, 1st century CE',
            'Figure 1: Vase en bronze, Ier siècle après J.-C.',
            'Figura 1: Vasija de bronce, siglo I d.C.',
        ];

        foreach ($languages as $index => $language) {
            $response = $this->postJson(route('picture-translation.store'), [
                'picture_id' => $picture->id,
                'language_code' => $language->code,
                'alt_text' => $altTexts[$index],
                'caption' => $captions[$index],
            ]);

            $response->assertCreated();
        }

        // Verify all translations exist
        $indexResponse = $this->getJson(route('picture-translation.index'));
        $indexResponse->assertOk();
        $indexResponse->assertJsonPath('meta.total', 3);
    }

    public function test_handles_accessibility_alt_text_variations()
    {
        $picture = Picture::factory()->create();

        $accessibilityAltTexts = [
            'Photograph showing ancient bronze vessel with decorative patterns',
            'Close-up detail of inscription on bronze artifact',
            'Archaeological site photograph with measuring scale',
            'X-ray image of internal structure of bronze object',
            'Conservation photograph showing restoration progress',
            'Microscopic detail of surface patination',
            'Color photograph under different lighting conditions',
            'Black and white archival photograph from 1925',
            'Digital reconstruction based on archaeological evidence',
            'Comparative image showing similar artifacts',
            'Technical drawing with measurements and annotations',
            'Infrared photograph revealing hidden details',
            'UV light photograph showing fluorescent materials',
            '3D model screenshot from multiple angles',
            'Historical photograph of excavation in progress',
        ];

        foreach ($accessibilityAltTexts as $index => $altText) {
            $newLanguage = Language::factory()->create(['code' => sprintf('a%02d', $index)]);

            $response = $this->postJson(route('picture-translation.store'), [
                'picture_id' => $picture->id,
                'language_code' => $newLanguage->code,
                'alt_text' => $altText,
                'caption' => "Figure {$index}: {$altText}",
            ]);

            $response->assertCreated(); // Should handle accessibility variations
        }
    }

    public function test_handles_caption_format_variations()
    {
        $picture = Picture::factory()->create();

        $captionFormats = [
            'Fig. 1: Bronze vessel (1st century CE)',
            'Plate II.3: Detail of decorative pattern',
            'Image 42: Archaeological context view',
            'Photo A.1: Before conservation treatment',
            'Diagram 5.2: Technical specifications',
            'Illustration 3: Artist reconstruction',
            'Map 7: Site location and surroundings',
            'Chart 12: Analytical results summary',
            'Table 4: Comparative measurements',
            'Appendix B.3: Supplementary documentation',
            'Figure A-1: Overview of collection',
            'Plate III.B.2: Detailed examination',
            'Schema 6: Classification system',
            'Drawing 8: Cross-sectional view',
            'Photograph 15: Current condition',
        ];

        foreach ($captionFormats as $index => $caption) {
            $newLanguage = Language::factory()->create(['code' => sprintf('c%02d', $index)]);

            $response = $this->postJson(route('picture-translation.store'), [
                'picture_id' => $picture->id,
                'language_code' => $newLanguage->code,
                'alt_text' => "Alt text for {$caption}",
                'caption' => $caption,
            ]);

            $response->assertCreated(); // Should handle caption format variations
        }
    }
}

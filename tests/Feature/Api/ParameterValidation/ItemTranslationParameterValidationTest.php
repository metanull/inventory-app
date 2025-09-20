<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Item;
use App\Models\ItemTranslation;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive parameter validation tests for ItemTranslation API endpoints
 */
class ItemTranslationParameterValidationTest extends TestCase
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
        $item = Item::factory()->create();
        ItemTranslation::factory()->count(22)->create(['item_id' => $item->id]);

        $response = $this->getJson(route('item-translation.index', [
            'page' => 4,
            'per_page' => 6,
        ]));

        $response->assertOk();
        $response->assertJsonPath('meta.current_page', 4);
        $response->assertJsonPath('meta.per_page', 6);
    }

    public function test_index_accepts_valid_include_parameters()
    {
        $item = Item::factory()->create();
        ItemTranslation::factory()->count(3)->create(['item_id' => $item->id]);

        $response = $this->getJson(route('item-translation.index', [
            'include' => 'item,language',
        ]));

        $response->assertOk();
    }

    public function test_index_rejects_invalid_include_parameters()
    {
        $item = Item::factory()->create();
        ItemTranslation::factory()->count(2)->create(['item_id' => $item->id]);

        $response = $this->getJson(route('item-translation.index', [
            'include' => 'invalid_relation,fake_item,non_existent',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['include']);
    }

    public function test_index_rejects_unexpected_query_parameters_currently()
    {
        $item = Item::factory()->create();
        ItemTranslation::factory()->count(3)->create(['item_id' => $item->id]);

        $response = $this->getJson(route('item-translation.index', [
            'page' => 1,
            'include' => 'item',
            'filter_by_collection' => 'ancient', // Not implemented
            'item_type' => 'artifact', // Not implemented
            'period' => 'medieval', // Not implemented
            'material' => 'bronze', // Not implemented
            'admin_access' => true,
            'debug_translations' => true,
            'export_format' => 'xml',
            'bulk_operation' => 'translate_all',
        ]));

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['filter_by_collection']);
    }

    // SHOW ENDPOINT TESTS
    public function test_show_accepts_valid_include_parameters()
    {
        $item = Item::factory()->create();
        $translation = ItemTranslation::factory()->create(['item_id' => $item->id]);

        $response = $this->getJson(route('item-translation.show', $translation).'?include=item,language');

        $response->assertOk();
    }

    public function test_show_rejects_unexpected_query_parameters_currently()
    {
        $item = Item::factory()->create();
        $translation = ItemTranslation::factory()->create(['item_id' => $item->id]);

        $response = $this->getJson(route('item-translation.show', $translation).'?include=item&show_metadata=true&curatorial_details=full');

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['show_metadata']);
    }

    // STORE ENDPOINT TESTS
    public function test_store_validates_required_fields()
    {
        $response = $this->postJson(route('item-translation.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['item_id', 'language_code']);
    }

    public function test_store_validates_item_id_exists()
    {
        $language = Language::factory()->create();

        $response = $this->postJson(route('item-translation.store'), [
            'item_id' => 'non-existent-uuid',
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['item_id']);
    }

    public function test_store_validates_language_code_exists()
    {
        $item = Item::factory()->create();

        $response = $this->postJson(route('item-translation.store'), [
            'item_id' => $item->id,
            'language_code' => 'xyz', // Invalid language code
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_code']);
    }

    public function test_store_validates_unique_combination()
    {
        $item = Item::factory()->create();
        $language = Language::factory()->create();
        ItemTranslation::factory()->create([
            'item_id' => $item->id,
            'language_code' => $language->code,
        ]);

        $response = $this->postJson(route('item-translation.store'), [
            'item_id' => $item->id,
            'language_code' => $language->code, // Duplicate combination
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['item_id']);
    }

    public function test_store_validates_item_id_uuid_format()
    {
        $language = Language::factory()->create();

        $response = $this->postJson(route('item-translation.store'), [
            'item_id' => 'not-a-uuid',
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['item_id']);
    }

    public function test_store_validates_language_code_format()
    {
        $item = Item::factory()->create();

        $response = $this->postJson(route('item-translation.store'), [
            'item_id' => $item->id,
            'language_code' => 'toolong', // Should be 3 characters
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_code']);
    }

    public function test_store_prohibits_id_field()
    {
        $item = Item::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('item-translation.store'), [
            'id' => 'some-uuid', // Should be prohibited
            'item_id' => $item->id,
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_store_accepts_valid_data()
    {
        $item = Item::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('item-translation.store'), [
            'item_id' => $item->id,
            'language_code' => $language->code,
            'title' => 'Translated Item Title',
            'description' => 'Translated item description',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.item_id', $item->id);
        $response->assertJsonPath('data.language_code', $language->code);
    }

    public function test_store_rejects_unexpected_request_parameters_currently()
    {
        $item = Item::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('item-translation.store'), [
            'item_id' => $item->id,
            'language_code' => $language->code,
            'title' => 'Test Item Translation',
            'description' => 'Test description',
            'unexpected_field' => 'should_be_rejected',
            'provenance' => 'Unknown origin', // Not implemented
            'acquisition_method' => 'Purchase', // Not implemented
            'cultural_period' => 'Renaissance', // Not implemented
            'materials' => 'Bronze, Gold', // Not implemented
            'dimensions' => '15x20x5 cm', // Not implemented
            'condition' => 'Excellent', // Not implemented
            'admin_created' => true,
            'debug_mode' => true,
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_validates_unique_combination_on_change()
    {
        $item1 = Item::factory()->create();
        $item2 = Item::factory()->create();
        $language = Language::factory()->create();

        $translation1 = ItemTranslation::factory()->create([
            'item_id' => $item1->id,
            'language_code' => $language->code,
        ]);

        ItemTranslation::factory()->create([
            'item_id' => $item2->id,
            'language_code' => $language->code,
        ]);

        $response = $this->putJson(route('item-translation.update', $translation1), [
            'item_id' => $item2->id, // Would create duplicate
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['item_id']);
    }

    public function test_update_prohibits_id_modification()
    {
        $item = Item::factory()->create();
        $translation = ItemTranslation::factory()->create(['item_id' => $item->id]);

        $response = $this->putJson(route('item-translation.update', $translation), [
            'id' => 'new-uuid', // Should be prohibited
            'item_id' => $item->id,
            'language_code' => $translation->language_code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_update_accepts_same_combination()
    {
        $item = Item::factory()->create();
        $translation = ItemTranslation::factory()->create(['item_id' => $item->id]);

        $response = $this->putJson(route('item-translation.update', $translation), [
            'item_id' => $translation->item_id, // Same combination
            'language_code' => $translation->language_code,
            'title' => 'Updated Item Title',
        ]);

        $response->assertOk();
    }

    public function test_update_rejects_unexpected_request_parameters_currently()
    {
        $item = Item::factory()->create();
        $translation = ItemTranslation::factory()->create(['item_id' => $item->id]);

        $response = $this->putJson(route('item-translation.update', $translation), [
            'item_id' => $translation->item_id,
            'language_code' => $translation->language_code,
            'title' => 'Updated Item Translation',
            'unexpected_field' => 'should_be_rejected',
            'change_status' => 'verified',
            'update_metadata' => 'complete',
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // EDGE CASE TESTS
    public function test_handles_unicode_characters_in_translation_fields()
    {
        $item = Item::factory()->create();

        $unicodeItemTitles = [
            'Artefact français ancien',
            'Артефакт русский древний',
            '古代日本語の遺物',
            'قطعة أثرية عربية',
            'Artefacto español antiguo',
            'Artefatto italiano antico',
            'Zabytek polski starożytny',
            'Αρχαίο ελληνικό τέχνημα',
            'Antik dansk genstand',
            'Ókori magyar tárgy',
        ];

        foreach ($unicodeItemTitles as $index => $title) {
            $newLanguage = Language::factory()->create(['code' => sprintf('%03d', $index)]);

            $response = $this->postJson(route('item-translation.store'), [
                'item_id' => $item->id,
                'language_code' => $newLanguage->code,
                'title' => $title,
                'description' => "Description for {$title}",
            ]);

            $response->assertCreated(); // Should handle Unicode gracefully
        }
    }

    public function test_handles_empty_and_null_optional_fields()
    {
        $item = Item::factory()->create();
        $translation = ItemTranslation::factory()->create(['item_id' => $item->id]);

        $testCases = [
            ['title' => null, 'description' => null],
            ['title' => '', 'description' => ''],
            ['title' => '   ', 'description' => '   '], // Whitespace only
            ['title' => 'Valid Title', 'description' => null],
            ['title' => null, 'description' => 'Valid Description'],
        ];

        foreach ($testCases as $data) {
            $updateData = array_merge([
                'item_id' => $translation->item_id,
                'language_code' => $translation->language_code,
            ], $data);

            $response = $this->putJson(route('item-translation.update', $translation), $updateData);

            // Should handle gracefully
            $this->assertContains($response->status(), [200, 422]);
        }
    }

    public function test_handles_very_long_translation_content()
    {
        $item = Item::factory()->create();
        $language = Language::factory()->create();

        $veryLongTitle = str_repeat('Very Long Item Title With Extended Cultural Description ', 20);
        $veryLongDescription = str_repeat('Very Long Item Description With Detailed Historical Context And Curatorial Analysis And Conservation Notes And Research Information ', 15);

        $response = $this->postJson(route('item-translation.store'), [
            'item_id' => $item->id,
            'language_code' => $language->code,
            'title' => $veryLongTitle,
            'description' => $veryLongDescription,
        ]);

        // Should handle gracefully
        $this->assertContains($response->status(), [201, 422]);
    }

    public function test_handles_array_injection_attempts()
    {
        $item = Item::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('item-translation.store'), [
            'item_id' => ['array' => 'instead_of_uuid'],
            'language_code' => ['malicious' => 'array'],
            'title' => ['injection' => 'attempt'],
            'description' => ['another' => 'injection'],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['item_id']);
    }

    public function test_pagination_with_many_translations()
    {
        $item = Item::factory()->create();
        ItemTranslation::factory()->count(75)->create(['item_id' => $item->id]);

        $testCases = [
            ['page' => 1, 'per_page' => 25],
            ['page' => 2, 'per_page' => 30],
            ['page' => 1, 'per_page' => 100], // Maximum
        ];

        foreach ($testCases as $params) {
            $response = $this->getJson(route('item-translation.index', $params));
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
            $response = $this->getJson(route('item-translation.index', $params));
            $response->assertUnprocessable();
        }
    }

    public function test_handles_special_characters_in_translation_content()
    {
        $item = Item::factory()->create();

        $specialCharTitles = [
            'Item "Ancient Vase" #123',
            "Item 'Bronze Sword' Rare",
            'Item & Accessories Set',
            'Item: Museum Piece',
            'Item (Restored)',
            'Item - Medieval Period',
            'Item @ Gallery',
            'Item #456',
            'Item 50% Complete',
            'Item $Priceless',
            'Item *Featured',
            'Item +Collection',
            'Item =Masterpiece',
            'Item |Unique',
        ];

        foreach ($specialCharTitles as $index => $title) {
            $newLanguage = Language::factory()->create(['code' => sprintf('i%02d', $index)]);

            $response = $this->postJson(route('item-translation.store'), [
                'item_id' => $item->id,
                'language_code' => $newLanguage->code,
                'title' => $title,
                'description' => "Description for {$title}",
            ]);

            // Should handle gracefully
            $this->assertContains($response->status(), [201, 422]);
        }
    }

    public function test_handles_item_translation_workflow()
    {
        $item = Item::factory()->create();
        $english = Language::factory()->create(['code' => 'eng']);
        $french = Language::factory()->create(['code' => 'fra']);
        $spanish = Language::factory()->create(['code' => 'spa']);

        // Create translations in different languages
        $languages = [$english, $french, $spanish];
        $titles = ['Ancient Vase', 'Vase Ancien', 'Jarrón Antiguo'];
        $descriptions = [
            'An ancient ceramic vase from the Roman period',
            'Un vase céramique ancien de la période romaine',
            'Un jarrón cerámico antiguo del período romano',
        ];

        foreach ($languages as $index => $language) {
            $response = $this->postJson(route('item-translation.store'), [
                'item_id' => $item->id,
                'language_code' => $language->code,
                'title' => $titles[$index],
                'description' => $descriptions[$index],
            ]);

            $response->assertCreated();
        }

        // Verify all translations exist
        $indexResponse = $this->getJson(route('item-translation.index'));
        $indexResponse->assertOk();
        $indexResponse->assertJsonPath('meta.total', 3);
    }

    public function test_handles_artifact_type_variations()
    {
        $item = Item::factory()->create();

        $artifactTypes = [
            'Ancient Pottery Vessel',
            'Bronze Age Tool',
            'Medieval Manuscript',
            'Renaissance Painting',
            'Archaeological Fragment',
            'Cultural Artifact',
            'Historical Document',
            'Ethnographic Object',
            'Natural History Specimen',
            'Scientific Instrument',
            'Decorative Arts Piece',
            'Religious Artifact',
            'Military Equipment',
            'Coin and Currency',
            'Textile and Clothing',
        ];

        foreach ($artifactTypes as $index => $title) {
            $newLanguage = Language::factory()->create(['code' => sprintf('t%02d', $index)]);

            $response = $this->postJson(route('item-translation.store'), [
                'item_id' => $item->id,
                'language_code' => $newLanguage->code,
                'title' => $title,
                'description' => "A detailed description of the {$title}",
            ]);

            $response->assertCreated(); // Should handle artifact type variations
        }
    }

    public function test_handles_museum_cataloging_terms()
    {
        $item = Item::factory()->create();

        $catalogingTerms = [
            'Accession Number: 2024.001',
            'Provenance: Private Collection',
            'Medium: Oil on Canvas',
            'Dimensions: 50 x 70 cm',
            'Date: c. 1650-1675',
            'Attribution: School of Rembrandt',
            'Condition: Good, minor restoration',
            'Exhibition History: Multiple',
            'Literature: Catalogued 1985',
            'Conservation: Cleaned 2020',
            'Location: Gallery 3, Wall A',
            'Insurance Value: Confidential',
            'Photographer: Museum Staff',
            'Rights: Museum Collection',
            'Keywords: Portrait, Baroque',
        ];

        foreach ($catalogingTerms as $index => $description) {
            $newLanguage = Language::factory()->create(['code' => sprintf('m%02d', $index)]);

            $response = $this->postJson(route('item-translation.store'), [
                'item_id' => $item->id,
                'language_code' => $newLanguage->code,
                'title' => "Museum Item #{$index}",
                'description' => $description,
            ]);

            $response->assertCreated(); // Should handle museum cataloging terms
        }
    }
}

<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Context;
use App\Models\Item;
use App\Models\ItemTranslation;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Clean parameter validation tests for ItemTranslation API endpoints
 * Tests ONLY what Form Requests actually validate - no made-up functionality
 */
class CleanItemTranslationParameterValidationTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    // INDEX ENDPOINT TESTS - Test only what IndexItemTranslationRequest actually validates
    public function test_index_validates_page_parameter_type()
    {
        $response = $this->getJson(route('item-translation.index', [
            'page' => 'not_a_number',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['page']);
    }

    public function test_index_validates_page_parameter_size()
    {
        $response = $this->getJson(route('item-translation.index', [
            'page' => 0, // Must be min:1
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['page']);
    }

    public function test_index_validates_per_page_parameter_type()
    {
        $response = $this->getJson(route('item-translation.index', [
            'per_page' => 'not_a_number',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['per_page']);
    }

    public function test_index_validates_per_page_parameter_size()
    {
        $response = $this->getJson(route('item-translation.index', [
            'per_page' => 101, // Must be max:100
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['per_page']);
    }

    public function test_index_validates_per_page_parameter_as_array()
    {
        $response = $this->getJson(route('item-translation.index', [
            'per_page' => ['not', 'integer'], // Should be integer, not array
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['per_page']);
    }

    public function test_index_validates_item_id_type()
    {
        $response = $this->getJson(route('item-translation.index', [
            'item_id' => 'not_a_uuid',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['item_id']);
    }

    public function test_index_validates_language_id_size()
    {
        $response = $this->getJson(route('item-translation.index', [
            'language_id' => 'toolong', // Should be exactly 3 characters
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['language_id']);
    }

    // SHOW ENDPOINT TESTS - Test only what ShowItemTranslationRequest actually validates
    public function test_show_validates_include_parameter_type()
    {
        $translation = ItemTranslation::factory()->create();

        $response = $this->getJson(route('item-translation.show', $translation).'?include[]=invalid_array');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['include']);
    }

    // STORE ENDPOINT TESTS - Test only what StoreItemTranslationRequest actually validates
    public function test_store_handles_empty_payload()
    {
        $response = $this->postJson(route('item-translation.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'item_id',
            'language_id',
            'context_id',
            'name',
            'description',
        ]);
    }

    public function test_store_validates_item_id_type()
    {
        $response = $this->postJson(route('item-translation.store'), [
            'item_id' => 'not_a_uuid',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['item_id']);
    }

    public function test_store_validates_item_id_exists()
    {
        $response = $this->postJson(route('item-translation.store'), [
            'item_id' => '12345678-1234-1234-1234-123456789012', // Valid UUID format but doesn't exist
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['item_id']);
    }

    public function test_store_validates_item_id_as_array()
    {
        $response = $this->postJson(route('item-translation.store'), [
            'item_id' => ['not', 'uuid'], // Should be string, not array
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['item_id']);
    }

    public function test_store_validates_language_id_type()
    {
        $item = Item::factory()->create();

        $response = $this->postJson(route('item-translation.store'), [
            'item_id' => $item->id,
            'language_id' => 123, // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_validates_language_id_size()
    {
        $item = Item::factory()->create();

        $response = $this->postJson(route('item-translation.store'), [
            'item_id' => $item->id,
            'language_id' => 'toolong', // Should be exactly 3 characters
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_validates_language_id_exists()
    {
        $item = Item::factory()->create();

        $response = $this->postJson(route('item-translation.store'), [
            'item_id' => $item->id,
            'language_id' => 'xyz', // Valid size but doesn't exist
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_validates_language_id_as_array()
    {
        $item = Item::factory()->create();

        $response = $this->postJson(route('item-translation.store'), [
            'item_id' => $item->id,
            'language_id' => ['not', 'string'], // Should be string, not array
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_validates_context_id_type()
    {
        $item = Item::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('item-translation.store'), [
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => 'not_a_uuid',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['context_id']);
    }

    public function test_store_validates_context_id_exists()
    {
        $item = Item::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('item-translation.store'), [
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => '12345678-1234-1234-1234-123456789012', // Valid UUID but doesn't exist
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['context_id']);
    }

    public function test_store_validates_name_type()
    {
        $item = Item::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $response = $this->postJson(route('item-translation.store'), [
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => ['array_not_string'], // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_store_validates_name_size()
    {
        $item = Item::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $response = $this->postJson(route('item-translation.store'), [
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => str_repeat('a', 256), // Exceeds max:255
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_store_validates_description_type()
    {
        $item = Item::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $response = $this->postJson(route('item-translation.store'), [
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Test Name',
            'description' => 12345, // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['description']);
    }

    public function test_store_validates_description_as_array()
    {
        $item = Item::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $response = $this->postJson(route('item-translation.store'), [
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Test Name',
            'description' => ['array', 'not', 'string'], // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['description']);
    }

    public function test_store_accepts_valid_data()
    {
        $item = Item::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $response = $this->postJson(route('item-translation.store'), [
            'item_id' => $item->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Test Item Name',
            'description' => 'Test item description',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.item_id', $item->id);
        $response->assertJsonPath('data.language_id', $language->id);
    }

    // UPDATE ENDPOINT TESTS - Test only what UpdateItemTranslationRequest actually validates
    public function test_update_handles_empty_payload()
    {
        $translation = ItemTranslation::factory()->create();

        $response = $this->putJson(route('item-translation.update', $translation), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'item_id',
            'language_id',
            'context_id',
            'name',
            'description',
        ]);
    }

    public function test_update_validates_wrong_parameter_types()
    {
        $translation = ItemTranslation::factory()->create();

        $response = $this->putJson(route('item-translation.update', $translation), [
            'item_id' => 'not_uuid',
            'language_id' => 123, // Should be string
            'context_id' => 'not_uuid',
            'name' => ['array'], // Should be string
            'description' => 456, // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'item_id',
            'language_id',
            'context_id',
            'name',
            'description',
        ]);
    }

    public function test_update_validates_wrong_parameter_sizes()
    {
        $translation = ItemTranslation::factory()->create();

        $response = $this->putJson(route('item-translation.update', $translation), [
            'item_id' => $translation->item_id,
            'language_id' => 'toolong', // Should be exactly 3 chars
            'context_id' => $translation->context_id,
            'name' => str_repeat('a', 256), // Should be max 255
            'description' => 'Valid description',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_id', 'name']);
    }

    public function test_update_validates_parameters_as_arrays()
    {
        $translation = ItemTranslation::factory()->create();

        $response = $this->putJson(route('item-translation.update', $translation), [
            'item_id' => [$translation->item_id], // Should be string
            'language_id' => [$translation->language_id], // Should be string
            'context_id' => [$translation->context_id], // Should be string
            'name' => ['array', 'name'], // Should be string
            'description' => ['array', 'description'], // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'item_id',
            'language_id',
            'context_id',
            'name',
            'description',
        ]);
    }

    public function test_update_accepts_valid_data()
    {
        $translation = ItemTranslation::factory()->create();

        $response = $this->putJson(route('item-translation.update', $translation), [
            'item_id' => $translation->item_id,
            'language_id' => $translation->language_id,
            'context_id' => $translation->context_id,
            'name' => 'Updated Item Name',
            'description' => 'Updated item description',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.name', 'Updated Item Name');
        $response->assertJsonPath('data.description', 'Updated item description');
    }
}

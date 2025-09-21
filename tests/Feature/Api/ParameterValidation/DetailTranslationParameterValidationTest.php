<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Context;
use App\Models\Detail;
use App\Models\DetailTranslation;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Clean parameter validation tests for DetailTranslation API endpoints
 * Tests ONLY what Form Requests actually validate - no made-up functionality
 */
class CleanDetailTranslationParameterValidationTest extends TestCase
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
    public function test_index_validates_page_parameter_type()
    {
        $response = $this->getJson(route('detail-translation.index', [
            'page' => 'not_a_number',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['page']);
    }

    public function test_index_validates_per_page_parameter_size()
    {
        $response = $this->getJson(route('detail-translation.index', [
            'per_page' => 101, // Must be max:100
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['per_page']);
    }

    // STORE ENDPOINT TESTS
    public function test_store_handles_empty_payload()
    {
        $response = $this->postJson(route('detail-translation.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'detail_id',
            'language_id',
            'context_id',
            'name',
            'description',
        ]);
    }

    public function test_store_validates_detail_id_type()
    {
        $response = $this->postJson(route('detail-translation.store'), [
            'detail_id' => 'not_a_uuid',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['detail_id']);
    }

    public function test_store_validates_detail_id_exists()
    {
        $response = $this->postJson(route('detail-translation.store'), [
            'detail_id' => '12345678-1234-1234-1234-123456789012',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['detail_id']);
    }

    public function test_store_validates_language_id_type()
    {
        $detail = Detail::factory()->create();

        $response = $this->postJson(route('detail-translation.store'), [
            'detail_id' => $detail->id,
            'language_id' => 123, // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_validates_language_id_size()
    {
        $detail = Detail::factory()->create();

        $response = $this->postJson(route('detail-translation.store'), [
            'detail_id' => $detail->id,
            'language_id' => 'toolong', // Should be exactly 3 characters
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_validates_context_id_type()
    {
        $detail = Detail::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('detail-translation.store'), [
            'detail_id' => $detail->id,
            'language_id' => $language->id,
            'context_id' => 'not_a_uuid',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['context_id']);
    }

    public function test_store_validates_name_type()
    {
        $detail = Detail::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $response = $this->postJson(route('detail-translation.store'), [
            'detail_id' => $detail->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => ['array', 'not', 'string'], // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_store_validates_name_size()
    {
        $detail = Detail::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $response = $this->postJson(route('detail-translation.store'), [
            'detail_id' => $detail->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => str_repeat('a', 256), // Exceeds max:255
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['name']);
    }

    public function test_store_validates_description_type()
    {
        $detail = Detail::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $response = $this->postJson(route('detail-translation.store'), [
            'detail_id' => $detail->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'title' => 'Test Title',
            'description' => 12345, // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['description']);
    }

    public function test_store_accepts_valid_data()
    {
        $detail = Detail::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $response = $this->postJson(route('detail-translation.store'), [
            'detail_id' => $detail->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'name' => 'Test Detail Name',
            'description' => 'Test detail description',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.detail_id', $detail->id);
        $response->assertJsonPath('data.language_id', $language->id);
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_handles_empty_payload()
    {
        $translation = DetailTranslation::factory()->create();

        $response = $this->putJson(route('detail-translation.update', $translation), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'detail_id',
            'language_id',
            'context_id',
            'name',
            'description',
        ]);
    }

    public function test_update_validates_wrong_parameter_types()
    {
        $translation = DetailTranslation::factory()->create();

        $response = $this->putJson(route('detail-translation.update', $translation), [
            'detail_id' => 'not_uuid',
            'language_id' => 123, // Should be string
            'context_id' => 'not_uuid',
            'name' => ['array'], // Should be string
            'description' => 456, // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'detail_id',
            'language_id',
            'context_id',
            'name',
            'description',
        ]);
    }

    public function test_update_accepts_valid_data()
    {
        $translation = DetailTranslation::factory()->create();

        $response = $this->putJson(route('detail-translation.update', $translation), [
            'detail_id' => $translation->detail_id,
            'language_id' => $translation->language_id,
            'context_id' => $translation->context_id,
            'name' => 'Updated Detail Name',
            'description' => 'Updated detail description',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.name', 'Updated Detail Name');
        $response->assertJsonPath('data.description', 'Updated detail description');
    }
}

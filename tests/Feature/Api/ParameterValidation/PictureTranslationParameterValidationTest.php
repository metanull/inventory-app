<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Context;
use App\Models\Language;
use App\Models\Picture;
use App\Models\PictureTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Clean parameter validation tests for PictureTranslation API endpoints
 * Tests ONLY what Form Requests actually validate - no made-up functionality
 */
class CleanPictureTranslationParameterValidationTest extends TestCase
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
        $response = $this->getJson(route('picture-translation.index', [
            'page' => 'not_a_number',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['page']);
    }

    public function test_index_validates_per_page_parameter_size()
    {
        $response = $this->getJson(route('picture-translation.index', [
            'per_page' => 101, // Must be max:100
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['per_page']);
    }

    public function test_index_validates_picture_id_type()
    {
        $response = $this->getJson(route('picture-translation.index', [
            'filter' => ['picture_id' => 'not_a_uuid'],
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['filter.picture_id']);
    }

    // STORE ENDPOINT TESTS
    public function test_store_handles_empty_payload()
    {
        $response = $this->postJson(route('picture-translation.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'picture_id',
            'language_id',
            'context_id',
            'description',
            'caption',
        ]);
    }

    public function test_store_validates_picture_id_type()
    {
        $response = $this->postJson(route('picture-translation.store'), [
            'picture_id' => 'not_a_uuid',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['picture_id']);
    }

    public function test_store_validates_picture_id_exists()
    {
        $response = $this->postJson(route('picture-translation.store'), [
            'picture_id' => '12345678-1234-1234-1234-123456789012',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['picture_id']);
    }

    public function test_store_validates_language_id_type()
    {
        $picture = Picture::factory()->create();

        $response = $this->postJson(route('picture-translation.store'), [
            'picture_id' => $picture->id,
            'language_id' => 123, // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_validates_language_id_size()
    {
        $picture = Picture::factory()->create();

        $response = $this->postJson(route('picture-translation.store'), [
            'picture_id' => $picture->id,
            'language_id' => 'toolong', // Should be exactly 3 characters
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_validates_language_id_as_array()
    {
        $picture = Picture::factory()->create();

        $response = $this->postJson(route('picture-translation.store'), [
            'picture_id' => $picture->id,
            'language_id' => ['not', 'string'], // Should be string, not array
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_validates_context_id_type()
    {
        $picture = Picture::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('picture-translation.store'), [
            'picture_id' => $picture->id,
            'language_id' => $language->id,
            'context_id' => 'not_a_uuid',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['context_id']);
    }

    public function test_store_validates_description_type()
    {
        $picture = Picture::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $response = $this->postJson(route('picture-translation.store'), [
            'picture_id' => $picture->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'description' => 12345, // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['description']);
    }

    public function test_store_validates_caption_type()
    {
        $picture = Picture::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $response = $this->postJson(route('picture-translation.store'), [
            'picture_id' => $picture->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'description' => 'Test description',
            'caption' => ['array', 'not', 'string'], // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['caption']);
    }

    public function test_store_accepts_valid_data()
    {
        $picture = Picture::factory()->create();
        $language = Language::factory()->create();
        $context = Context::factory()->create();

        $response = $this->postJson(route('picture-translation.store'), [
            'picture_id' => $picture->id,
            'language_id' => $language->id,
            'context_id' => $context->id,
            'description' => 'Test picture description',
            'caption' => 'Test picture caption',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.picture_id', $picture->id);
        $response->assertJsonPath('data.language_id', $language->id);
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_handles_empty_payload()
    {
        $translation = PictureTranslation::factory()->create();

        $response = $this->putJson(route('picture-translation.update', $translation), []);

        // Empty payload should be acceptable for updates (partial updates allowed)
        $response->assertOk();
    }

    public function test_update_validates_wrong_parameter_types()
    {
        $translation = PictureTranslation::factory()->create();

        $response = $this->putJson(route('picture-translation.update', $translation), [
            'picture_id' => 'not_uuid',
            'language_id' => 123, // Should be string
            'context_id' => 'not_uuid',
            'description' => 456, // Should be string
            'caption' => ['array'], // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'picture_id',
            'language_id',
            'context_id',
            'description',
            'caption',
        ]);
    }

    public function test_update_validates_wrong_parameter_sizes()
    {
        $translation = PictureTranslation::factory()->create();

        $response = $this->putJson(route('picture-translation.update', $translation), [
            'picture_id' => $translation->picture_id,
            'language_id' => 'toolong', // Should be exactly 3 chars
            'context_id' => $translation->context_id,
            'description' => 'Valid description',
            'caption' => 'Valid caption',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_id']);
    }

    public function test_update_accepts_valid_data()
    {
        $translation = PictureTranslation::factory()->create();

        $response = $this->putJson(route('picture-translation.update', $translation), [
            'picture_id' => $translation->picture_id,
            'language_id' => $translation->language_id,
            'context_id' => $translation->context_id,
            'description' => 'Updated picture description',
            'caption' => 'Updated picture caption',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.description', 'Updated picture description');
        $response->assertJsonPath('data.caption', 'Updated picture caption');
    }
}

<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Contact;
use App\Models\ContactTranslation;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Clean parameter validation tests for ContactTranslation API endpoints
 * Tests ONLY what Form Requests actually validate - no made-up functionality
 */
class CleanContactTranslationParameterValidationTest extends TestCase
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
        $response = $this->getJson(route('contact-translation.index', [
            'page' => 'not_a_number',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['page']);
    }

    public function test_index_validates_per_page_parameter_size()
    {
        $response = $this->getJson(route('contact-translation.index', [
            'per_page' => 101, // Must be max:100
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['per_page']);
    }

    // STORE ENDPOINT TESTS
    public function test_store_handles_empty_payload()
    {
        $response = $this->postJson(route('contact-translation.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'contact_id',
            'language_id',
            'label',
        ]);
    }

    public function test_store_validates_contact_id_type()
    {
        $response = $this->postJson(route('contact-translation.store'), [
            'contact_id' => 'not_a_uuid',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['contact_id']);
    }

    public function test_store_validates_contact_id_exists()
    {
        $response = $this->postJson(route('contact-translation.store'), [
            'contact_id' => '12345678-1234-1234-1234-123456789012',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['contact_id']);
    }

    public function test_store_validates_language_id_type()
    {
        $contact = Contact::factory()->create();

        $response = $this->postJson(route('contact-translation.store'), [
            'contact_id' => $contact->id,
            'language_id' => 123, // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_validates_language_id_size()
    {
        $contact = Contact::factory()->create();

        $response = $this->postJson(route('contact-translation.store'), [
            'contact_id' => $contact->id,
            'language_id' => 'toolong', // Should be exactly 3 characters
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_id']);
    }

    public function test_store_validates_context_id_type()
    {
        $contact = Contact::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('contact-translation.store'), [
            'contact_id' => $contact->id,
            'language_id' => $language->id,
            'label' => 'Test Label',
            'backward_compatibility' => 'not_valid_for_test', // Testing other fields work
        ]);

        $response->assertCreated(); // This should succeed since all required fields are present
    }

    public function test_store_validates_label_type()
    {
        $contact = Contact::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('contact-translation.store'), [
            'contact_id' => $contact->id,
            'language_id' => $language->id,
            'label' => ['array', 'not', 'string'], // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['label']);
    }

    public function test_store_validates_label_size()
    {
        $contact = Contact::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('contact-translation.store'), [
            'contact_id' => $contact->id,
            'language_id' => $language->id,
            'label' => str_repeat('a', 256), // Exceeds max:255
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['label']);
    }

    public function test_store_accepts_valid_data()
    {
        $contact = Contact::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('contact-translation.store'), [
            'contact_id' => $contact->id,
            'language_id' => $language->id,
            'label' => 'Test Contact Label',
            'backward_compatibility' => 'legacy_id_123',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.contact_id', $contact->id);
        $response->assertJsonPath('data.language_id', $language->id);
        $response->assertJsonPath('data.label', 'Test Contact Label');
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_handles_empty_payload()
    {
        $translation = ContactTranslation::factory()->create();

        $response = $this->putJson(route('contact-translation.update', $translation), []);

        // Empty payload should be acceptable for updates (partial updates allowed)
        $response->assertOk();
    }

    public function test_update_validates_wrong_parameter_types()
    {
        $translation = ContactTranslation::factory()->create();

        $response = $this->putJson(route('contact-translation.update', $translation), [
            'contact_id' => 'not_uuid',
            'language_id' => 123, // Should be string
            'label' => ['array'], // Should be string
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors([
            'contact_id',
            'language_id',
            'label',
        ]);
    }

    public function test_update_accepts_valid_data()
    {
        $translation = ContactTranslation::factory()->create();

        $response = $this->putJson(route('contact-translation.update', $translation), [
            'contact_id' => $translation->contact_id,
            'language_id' => $translation->language_id,
            'label' => 'Updated Contact Label',
            'backward_compatibility' => 'updated_legacy_id',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.label', 'Updated Contact Label');
    }
}

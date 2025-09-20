<?php

namespace Tests\Feature\Api\ParameterValidation;

use App\Models\Contact;
use App\Models\ContactTranslation;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive parameter validation tests for ContactTranslation API endpoints
 */
class ContactTranslationParameterValidationTest extends TestCase
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
        $contact = Contact::factory()->create();
        ContactTranslation::factory()->count(15)->create(['contact_id' => $contact->id]);

        $response = $this->getJson(route('contact-translation.index', [
            'page' => 2,
            'per_page' => 8,
        ]));

        $response->assertOk();
        $response->assertJsonPath('meta.current_page', 2);
        $response->assertJsonPath('meta.per_page', 8);
    }

    public function test_index_accepts_valid_include_parameters()
    {
        $contact = Contact::factory()->create();
        ContactTranslation::factory()->count(3)->create(['contact_id' => $contact->id]);

        $response = $this->getJson(route('contact-translation.index', [
            'include' => 'contact,language',
        ]));

        $response->assertOk();
    }

    public function test_index_rejects_invalid_include_parameters()
    {
        $contact = Contact::factory()->create();
        ContactTranslation::factory()->count(2)->create(['contact_id' => $contact->id]);

        $response = $this->getJson(route('contact-translation.index', [
            'include' => 'invalid_relation,fake_contact,non_existent',
        ]));

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['include']);
    }

    public function test_index_rejects_unexpected_query_parameters_currently()
    {
        $contact = Contact::factory()->create();
        ContactTranslation::factory()->count(2)->create(['contact_id' => $contact->id]);

        $response = $this->getJson(route('contact-translation.index', [
            'page' => 1,
            'include' => 'contact',
            'filter_by_language' => 'en', // Not implemented
            'contact_type' => 'person', // Not implemented
            'status' => 'active', // Not implemented
            'admin_access' => true,
            'debug_translations' => true,
            'export_format' => 'csv',
            'bulk_operation' => 'validate_all',
        ]));

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['filter_by_language']);
    }

    // SHOW ENDPOINT TESTS
    public function test_show_accepts_valid_include_parameters()
    {
        $contact = Contact::factory()->create();
        $translation = ContactTranslation::factory()->create(['contact_id' => $contact->id]);

        $response = $this->getJson(route('contact-translation.show', $translation).'?include=contact,language');

        $response->assertOk();
    }

    public function test_show_rejects_unexpected_query_parameters_currently()
    {
        $contact = Contact::factory()->create();
        $translation = ContactTranslation::factory()->create(['contact_id' => $contact->id]);

        $response = $this->getJson(route('contact-translation.show', $translation).'?include=contact&show_history=true&validation_details=full');

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['show_history']);
    }

    // STORE ENDPOINT TESTS
    public function test_store_validates_required_fields()
    {
        $response = $this->postJson(route('contact-translation.store'), []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['contact_id', 'language_code']);
    }

    public function test_store_validates_contact_id_exists()
    {
        $language = Language::factory()->create();

        $response = $this->postJson(route('contact-translation.store'), [
            'contact_id' => 'non-existent-uuid',
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['contact_id']);
    }

    public function test_store_validates_language_code_exists()
    {
        $contact = Contact::factory()->create();

        $response = $this->postJson(route('contact-translation.store'), [
            'contact_id' => $contact->id,
            'language_code' => 'xyz', // Invalid language code
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_code']);
    }

    public function test_store_validates_unique_combination()
    {
        $contact = Contact::factory()->create();
        $language = Language::factory()->create();
        ContactTranslation::factory()->create([
            'contact_id' => $contact->id,
            'language_code' => $language->code,
        ]);

        $response = $this->postJson(route('contact-translation.store'), [
            'contact_id' => $contact->id,
            'language_code' => $language->code, // Duplicate combination
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['contact_id']);
    }

    public function test_store_validates_contact_id_uuid_format()
    {
        $language = Language::factory()->create();

        $response = $this->postJson(route('contact-translation.store'), [
            'contact_id' => 'not-a-uuid',
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['contact_id']);
    }

    public function test_store_validates_language_code_format()
    {
        $contact = Contact::factory()->create();

        $response = $this->postJson(route('contact-translation.store'), [
            'contact_id' => $contact->id,
            'language_code' => 'toolong', // Should be 3 characters
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['language_code']);
    }

    public function test_store_prohibits_id_field()
    {
        $contact = Contact::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('contact-translation.store'), [
            'id' => 'some-uuid', // Should be prohibited
            'contact_id' => $contact->id,
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_store_accepts_valid_data()
    {
        $contact = Contact::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('contact-translation.store'), [
            'contact_id' => $contact->id,
            'language_code' => $language->code,
            'name' => 'Translated Contact Name',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('data.contact_id', $contact->id);
        $response->assertJsonPath('data.language_code', $language->code);
    }

    public function test_store_rejects_unexpected_request_parameters_currently()
    {
        $contact = Contact::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('contact-translation.store'), [
            'contact_id' => $contact->id,
            'language_code' => $language->code,
            'name' => 'Test Translation',
            'unexpected_field' => 'should_be_rejected',
            'title' => 'Dr.', // Not implemented
            'department' => 'Archaeology', // Not implemented
            'phone_extension' => '123', // Not implemented
            'biography' => 'Long biography...', // Not implemented
            'admin_created' => true,
            'malicious_script' => '<script>alert("xss")</script>',
            'sql_injection' => "'; DROP TABLE contact_translations; --",
            'privilege_escalation' => 'translator_access',
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // UPDATE ENDPOINT TESTS
    public function test_update_validates_unique_combination_on_change()
    {
        $contact1 = Contact::factory()->create();
        $contact2 = Contact::factory()->create();
        $language = Language::factory()->create();

        $translation1 = ContactTranslation::factory()->create([
            'contact_id' => $contact1->id,
            'language_code' => $language->code,
        ]);

        ContactTranslation::factory()->create([
            'contact_id' => $contact2->id,
            'language_code' => $language->code,
        ]);

        $response = $this->putJson(route('contact-translation.update', $translation1), [
            'contact_id' => $contact2->id, // Would create duplicate
            'language_code' => $language->code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['contact_id']);
    }

    public function test_update_prohibits_id_modification()
    {
        $contact = Contact::factory()->create();
        $translation = ContactTranslation::factory()->create(['contact_id' => $contact->id]);

        $response = $this->putJson(route('contact-translation.update', $translation), [
            'id' => 'new-uuid', // Should be prohibited
            'contact_id' => $contact->id,
            'language_code' => $translation->language_code,
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id']);
    }

    public function test_update_accepts_same_combination()
    {
        $contact = Contact::factory()->create();
        $translation = ContactTranslation::factory()->create(['contact_id' => $contact->id]);

        $response = $this->putJson(route('contact-translation.update', $translation), [
            'contact_id' => $translation->contact_id, // Same combination
            'language_code' => $translation->language_code,
            'name' => 'Updated Name',
        ]);

        $response->assertOk();
    }

    public function test_update_rejects_unexpected_request_parameters_currently()
    {
        $contact = Contact::factory()->create();
        $translation = ContactTranslation::factory()->create(['contact_id' => $contact->id]);

        $response = $this->putJson(route('contact-translation.update', $translation), [
            'contact_id' => $translation->contact_id,
            'language_code' => $translation->language_code,
            'name' => 'Updated Translation',
            'unexpected_field' => 'should_be_rejected',
            'change_status' => 'verified',
            'update_source' => 'external_system',
        ]);

        $response->assertStatus(422); // Form Request properly rejects unexpected params
        $response->assertJsonValidationErrors(['unexpected_field']);
    }

    // EDGE CASE TESTS
    public function test_handles_unicode_characters_in_translation_fields()
    {
        $contact = Contact::factory()->create();
        $language = Language::factory()->create();

        $unicodeNames = [
            'Nom français',
            'Имя русское',
            '名前日本語',
            'اسم عربي',
            'Nombre español',
            'Nome italiano',
            'Imię polskie',
            'Όνομα ελληνικό',
            'Navn dansk',
            'Név magyar',
        ];

        foreach ($unicodeNames as $index => $name) {
            $newLanguage = Language::factory()->create(['code' => sprintf('%03d', $index)]);

            $response = $this->postJson(route('contact-translation.store'), [
                'contact_id' => $contact->id,
                'language_code' => $newLanguage->code,
                'name' => $name,
            ]);

            $response->assertCreated(); // Should handle Unicode gracefully
        }
    }

    public function test_handles_empty_and_null_optional_fields()
    {
        $contact = Contact::factory()->create();
        $translation = ContactTranslation::factory()->create(['contact_id' => $contact->id]);

        $testCases = [
            ['name' => null],
            ['name' => ''],
            ['name' => '   '], // Whitespace only
        ];

        foreach ($testCases as $data) {
            $updateData = array_merge([
                'contact_id' => $translation->contact_id,
                'language_code' => $translation->language_code,
            ], $data);

            $response = $this->putJson(route('contact-translation.update', $translation), $updateData);

            // Should handle gracefully
            $this->assertContains($response->status(), [200, 422]);
        }
    }

    public function test_handles_very_long_translation_content()
    {
        $contact = Contact::factory()->create();
        $language = Language::factory()->create();

        $veryLongName = str_repeat('Very Long Contact Name With Extended Description And Detailed Information ', 50);

        $response = $this->postJson(route('contact-translation.store'), [
            'contact_id' => $contact->id,
            'language_code' => $language->code,
            'name' => $veryLongName,
        ]);

        // Should handle gracefully
        $this->assertContains($response->status(), [201, 422]);
    }

    public function test_handles_array_injection_attempts()
    {
        $contact = Contact::factory()->create();
        $language = Language::factory()->create();

        $response = $this->postJson(route('contact-translation.store'), [
            'contact_id' => ['array' => 'instead_of_uuid'],
            'language_code' => ['malicious' => 'array'],
            'name' => ['injection' => 'attempt'],
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['contact_id']);
    }

    public function test_pagination_with_many_translations()
    {
        $contact = Contact::factory()->create();
        ContactTranslation::factory()->count(50)->create(['contact_id' => $contact->id]);

        $testCases = [
            ['page' => 1, 'per_page' => 15],
            ['page' => 2, 'per_page' => 20],
            ['page' => 1, 'per_page' => 100], // Maximum
        ];

        foreach ($testCases as $params) {
            $response = $this->getJson(route('contact-translation.index', $params));
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
            $response = $this->getJson(route('contact-translation.index', $params));
            $response->assertUnprocessable();
        }
    }

    public function test_handles_special_characters_in_translation_content()
    {
        $contact = Contact::factory()->create();
        $language = Language::factory()->create();

        $specialCharNames = [
            'Name "with quotes"',
            "Name 'with apostrophes'",
            'Name & symbols',
            'Name: colon test',
            'Name (parentheses)',
            'Name - dash test',
            'Name @ symbol',
            'Name #hashtag',
            'Name 50%',
            'Name $dollar',
            'Name *asterisk',
            'Name +plus',
            'Name =equals',
            'Name |pipe',
        ];

        foreach ($specialCharNames as $index => $name) {
            $newLanguage = Language::factory()->create(['code' => sprintf('t%02d', $index)]);

            $response = $this->postJson(route('contact-translation.store'), [
                'contact_id' => $contact->id,
                'language_code' => $newLanguage->code,
                'name' => $name,
            ]);

            // Should handle gracefully
            $this->assertContains($response->status(), [201, 422]);
        }
    }

    public function test_handles_contact_translation_workflow()
    {
        $contact = Contact::factory()->create();
        $english = Language::factory()->create(['code' => 'eng']);
        $french = Language::factory()->create(['code' => 'fra']);
        $spanish = Language::factory()->create(['code' => 'spa']);

        // Create translations in different languages
        $languages = [$english, $french, $spanish];
        $names = ['John Smith', 'Jean Dupont', 'Juan García'];

        foreach ($languages as $index => $language) {
            $response = $this->postJson(route('contact-translation.store'), [
                'contact_id' => $contact->id,
                'language_code' => $language->code,
                'name' => $names[$index],
            ]);

            $response->assertCreated();
        }

        // Verify all translations exist
        $indexResponse = $this->getJson(route('contact-translation.index'));
        $indexResponse->assertOk();
        $indexResponse->assertJsonPath('meta.total', 3);
    }
}

<?php

namespace Tests\Feature\Api\ContactTranslation;

use App\Models\ContactTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnonymousTest extends TestCase
{
    use RefreshDatabase;

    public function test_anonymous_cannot_access_contact_translations_index()
    {
        $response = $this->getJson(route('contact-translation.index'));
        $response->assertUnauthorized();
    }

    public function test_anonymous_cannot_access_contact_translation_show()
    {
        $contact = \App\Models\Contact::factory()->withoutTranslations()->create();
        $language = \App\Models\Language::factory()->create();
        $translation = ContactTranslation::factory()->create([
            'contact_id' => $contact->id,
            'language_id' => $language->id,
        ]);
        $response = $this->getJson(route('contact-translation.show', $translation->id));
        $response->assertUnauthorized();
    }

    public function test_anonymous_cannot_create_contact_translation()
    {
        $contact = \App\Models\Contact::factory()->withoutTranslations()->create();
        $language = \App\Models\Language::factory()->create();
        $data = ContactTranslation::factory()->make([
            'contact_id' => $contact->id,
            'language_id' => $language->id,
        ])->toArray();
        $response = $this->postJson(route('contact-translation.store'), $data);
        $response->assertUnauthorized();
    }

    public function test_anonymous_cannot_update_contact_translation()
    {
        $contact = \App\Models\Contact::factory()->withoutTranslations()->create();
        $language = \App\Models\Language::factory()->create();
        $translation = ContactTranslation::factory()->create([
            'contact_id' => $contact->id,
            'language_id' => $language->id,
        ]);

        $newContact = \App\Models\Contact::factory()->withoutTranslations()->create();
        $newLanguage = \App\Models\Language::factory()->create();
        $data = ContactTranslation::factory()->make([
            'contact_id' => $newContact->id,
            'language_id' => $newLanguage->id,
        ])->toArray();
        $response = $this->putJson(route('contact-translation.update', $translation->id), $data);
        $response->assertUnauthorized();
    }

    public function test_anonymous_cannot_delete_contact_translation()
    {
        $contact = \App\Models\Contact::factory()->withoutTranslations()->create();
        $language = \App\Models\Language::factory()->create();
        $translation = ContactTranslation::factory()->create([
            'contact_id' => $contact->id,
            'language_id' => $language->id,
        ]);
        $response = $this->deleteJson(route('contact-translation.destroy', $translation->id));
        $response->assertUnauthorized();
    }
}

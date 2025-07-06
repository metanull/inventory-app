<?php

namespace Tests\Feature\Api\ContactTranslation;

use App\Models\Contact;
use App\Models\ContactTranslation;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_create_contact_translation()
    {
        $contact = Contact::factory()->create();
        $language = Language::factory()->create();

        $data = [
            'contact_id' => $contact->id,
            'language_id' => $language->id,
            'label' => 'Test Label',
        ];

        $response = $this->postJson(route('contact-translation.store'), $data);

        $response->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'contact_id',
                    'language_id',
                    'label',
                    'created_at',
                    'updated_at',
                ],
            ]);

        $this->assertDatabaseHas('contact_translations', $data);
    }

    public function test_requires_contact_id()
    {
        $language = \App\Models\Language::factory()->create();
        $data = ContactTranslation::factory()->make([
            'language_id' => $language->id,
        ])->except('contact_id');
        $response = $this->postJson(route('contact-translation.store'), $data);
        $response->assertUnprocessable();
    }

    public function test_requires_language_id()
    {
        $contact = \App\Models\Contact::factory()->withoutTranslations()->create();
        $data = ContactTranslation::factory()->make([
            'contact_id' => $contact->id,
        ])->except('language_id');
        $response = $this->postJson(route('contact-translation.store'), $data);
        $response->assertUnprocessable();
    }
}

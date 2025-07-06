<?php

namespace Tests\Feature\Api\ContactTranslation;

use App\Models\ContactTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShowTest extends TestCase
{
    use RefreshDatabase;

    protected ?User $user = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_can_show_contact_translation()
    {
        $contact = \App\Models\Contact::factory()->withoutTranslations()->create();
        $language = \App\Models\Language::factory()->create();
        $translation = ContactTranslation::factory()->create([
            'contact_id' => $contact->id,
            'language_id' => $language->id,
        ]);
        $response = $this->getJson(route('contact-translation.show', $translation->id));

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'contact_id',
                    'language_id',
                    'label',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonPath('data.id', $translation->id);
    }

    public function test_returns_404_for_nonexistent_contact_translation()
    {
        $response = $this->getJson(route('contact-translation.show', 'nonexistent-id'));
        $response->assertNotFound();
    }
}
